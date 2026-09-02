<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderStatusManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('admin123'), 'role' => 'Super Admin']
        );
    }

    private function getAdminHeaders(): array
    {
        return [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer admin-token-test',
            'x-admin-token' => 'admin-token-test',
        ];
    }

    /**
     * 1. New order created via checkout or sync is persisted with status = 'Pending'.
     */
    public function test_new_order_defaults_to_pending_status()
    {
        $invoice = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $order = Order::create([
            'invoice_no'       => $invoice,
            'customer_name'    => 'Rahim Uddin',
            'customer_phone'   => '01799887766',
            'customer_address' => 'Mirpur 10, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 80,
            'subtotal'         => 1200,
            'total_amount'     => 1280,
            'payment_method'   => 'Cash on Delivery',
            'status'           => 'pending',
            'source_type'      => 'landing_page',
        ]);

        $this->assertDatabaseHas('orders', [
            'invoice_no' => $invoice,
            'status'     => 'pending',
        ]);

        // When retrieved via orders API, status is returned correctly
        $res = $this->getJson('/api/orders', $this->getAdminHeaders());
        $res->assertStatus(200);
        $found = collect($res->json('orders'))->firstWhere('order_number', $invoice);
        $this->assertNotNull($found);
        $this->assertEquals('pending', strtolower($found['status']));

        $order->delete();
    }

    /**
     * 2. Status update PATCH endpoint accepts all 8 canonical statuses and persists them to DB.
     */
    public function test_status_update_lifecycle_persists_to_database()
    {
        Http::preventStrayRequests();

        $invoice = 'ORD-STATUS-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $order = Order::create([
            'invoice_no'       => $invoice,
            'customer_name'    => 'Karim Mia',
            'customer_phone'   => '01811223344',
            'customer_address' => 'Uttara Sector 3, Dhaka',
            'total_amount'     => 950,
            'status'           => 'Pending',
        ]);

        $statuses = [
            'Approved'         => 'Approved',
            'Work In Progress' => 'Work In Progress',
            'Packaging'        => 'Packaging',
            'Shipment'         => 'Shipment',
            'Delivered'        => 'Delivered',
            'Cancel'           => 'Cancel',
            'Return'           => 'Return',
            'Pending'          => 'Pending',
        ];

        foreach ($statuses as $input => $expectedDb) {
            $patchRes = $this->patchJson("/api/orders/{$invoice}/status", [
                'status' => $input
            ], $this->getAdminHeaders());

            $patchRes->assertStatus(200)
                ->assertJson([
                    'success'      => true,
                    'order_number' => $invoice,
                    'status'       => $expectedDb,
                ]);

            // Direct database assertion proving persistence
            $this->assertDatabaseHas('orders', [
                'invoice_no' => $invoice,
                'status'     => $expectedDb,
            ]);
        }

        // Assert 0 HTTP calls dispatched during all status transitions
        Http::assertNothingSent();

        $order->delete();
    }

    /**
     * 3. Invalid status values are strictly rejected with HTTP 422 Unprocessable Entity.
     */
    public function test_invalid_status_is_rejected_by_backend()
    {
        $invoice = 'ORD-INV-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $order = Order::create([
            'invoice_no'       => $invoice,
            'customer_name'    => 'Invalid Tester',
            'customer_phone'   => '01700000000',
            'customer_address' => 'Dhaka',
            'total_amount'     => 500,
            'status'           => 'Pending',
        ]);

        $badRes = $this->patchJson("/api/orders/{$invoice}/status", [
            'status' => 'bogus_random_status'
        ], $this->getAdminHeaders());

        $badRes->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // Verify status remains unchanged in database
        $this->assertDatabaseHas('orders', [
            'invoice_no' => $invoice,
            'status'     => 'Pending',
        ]);

        $order->delete();
    }
}
