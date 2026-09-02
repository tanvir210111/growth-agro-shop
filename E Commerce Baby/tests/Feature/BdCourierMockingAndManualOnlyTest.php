<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BdCourierMockingAndManualOnlyTest extends TestCase
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
     * Proves that automated tests make ZERO external BD Courier API requests.
     */
    public function test_automated_tests_make_zero_external_bd_courier_api_requests()
    {
        // Prevent any real outgoing network requests during tests
        Http::preventStrayRequests();

        Http::fake([
            'https://api.bdcourier.com/*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'total_parcel'     => 10,
                    'success_parcel'   => 9,
                    'cancelled_parcel' => 1,
                    'success_ratio'    => 90,
                    'courier_breakdown'=> [],
                    'reports'          => []
                ]
            ], 200),
        ]);

        $res = $this->getJson('/api/admin/fraud/courier-check?phone=01711223344', $this->getAdminHeaders());
        $res->assertStatus(200)
            ->assertJson(['success' => true]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.bdcourier.com/courier-check');
        });
    }

    /**
     * Proves that Orders page load, Dashboard load, refresh, risk filters make ZERO calls to BD Courier.
     */
    public function test_orders_page_and_dashboard_make_zero_courier_api_calls()
    {
        // Prevent any outgoing request. If any code tries to call external API, test will fail.
        Http::preventStrayRequests();

        // 1. Load orders
        $resOrders = $this->getJson('/api/orders', $this->getAdminHeaders());
        $resOrders->assertStatus(200);

        // 2. Load fraud overview
        $resOverview = $this->getJson('/api/admin/fraud/overview', $this->getAdminHeaders());
        $resOverview->assertStatus(200);

        // 3. Risk filters
        foreach (['high', 'medium', 'low', 'not_assessed'] as $risk) {
            $resRisk = $this->getJson("/api/orders?risk={$risk}", $this->getAdminHeaders());
            $resRisk->assertStatus(200);
        }

        // Assert that zero HTTP requests were dispatched
        Http::assertNothingSent();
    }
}
