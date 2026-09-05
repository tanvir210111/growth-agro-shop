<?php

namespace Tests\Feature;

use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Services\MetaConversionApiService;
use App\Services\MetaEventIdService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaEventIdPhase3Test extends TestCase
{
    protected MetaEventIdService $eventIdService;
    protected MetaConversionApiService $apiService;
    protected MetaTrackingConfigService $configService;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        $this->eventIdService = app(MetaEventIdService::class);
        $this->configService = app(MetaTrackingConfigService::class);
        $this->apiService = app(MetaConversionApiService::class);

        $this->configService->invalidateCache();
    }

    /**
     * Helper to create a dynamic test pixel without hardcoding any ID.
     */
    protected function createDynamicTestPixel(array $overrides = []): MetaPixel
    {
        $dynamicId = '77' . str_pad((string) mt_rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT);
        $dynamicToken = 'EAAG_dyn_' . bin2hex(random_bytes(16));

        $pixel = MetaPixel::create(array_merge([
            'pixel_name'      => 'Dynamic Test Pixel',
            'pixel_id'        => $dynamicId,
            'access_token'    => $dynamicToken,
            'test_event_code' => 'TEST_P3_DYN',
            'is_active'       => true,
            'is_default'      => true,
        ], $overrides));

        $settings = MetaTrackingSetting::current();
        $settings->update([
            'active_pixel_id'                   => $pixel->id,
            'is_enabled'                        => true,
            'server_pageview_enabled'           => true,
            'server_add_to_cart_enabled'        => true,
            'server_initiate_checkout_enabled'  => true,
            'server_purchase_enabled'           => true,
            'browser_pageview_enabled'          => true,
            'browser_add_to_cart_enabled'       => true,
            'browser_initiate_checkout_enabled' => true,
            'browser_purchase_enabled'          => true,
        ]);

        $this->configService->invalidateCache();

        return $pixel;
    }

    /**
     * 1. PageView generates valid unique event ID.
     */
    public function test_pageview_generates_valid_unique_event_id(): void
    {
        $id1 = $this->eventIdService->generateForEvent('PageView');
        $id2 = $this->eventIdService->generateForEvent('PageView');

        $this->assertNotEmpty($id1);
        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id1, $id2, 'Consecutive PageView calls must generate distinct logical event IDs');
        $this->assertTrue($this->eventIdService->isValid($id1));
        $this->assertTrue($this->eventIdService->isValid($id2));
        $this->assertStringStartsWith('pv_', $id1);
    }

    /**
     * 2. AddToCart generates valid unique event ID.
     */
    public function test_add_to_cart_generates_valid_unique_event_id(): void
    {
        $id1 = $this->eventIdService->generateForEvent('AddToCart');
        $id2 = $this->eventIdService->generateForEvent('AddToCart');

        $this->assertNotEmpty($id1);
        $this->assertNotEquals($id1, $id2);
        $this->assertTrue($this->eventIdService->isValid($id1));
        $this->assertStringStartsWith('atc_', $id1);
    }

    /**
     * 3. InitiateCheckout generates valid unique event ID.
     */
    public function test_initiate_checkout_generates_valid_unique_event_id(): void
    {
        $id1 = $this->eventIdService->generateForEvent('InitiateCheckout');
        $id2 = $this->eventIdService->generateForEvent('InitiateCheckout');

        $this->assertNotEmpty($id1);
        $this->assertNotEquals($id1, $id2);
        $this->assertTrue($this->eventIdService->isValid($id1));
        $this->assertStringStartsWith('ic_', $id1);
    }

    /**
     * 4. Purchase event ID is deterministic: purchase_{ORDER_NUMBER}.
     */
    public function test_purchase_event_id_is_deterministic(): void
    {
        $orderNumber = 'CB-20260905-ABCD99';
        $eventId1 = $this->eventIdService->generatePurchaseEventId($orderNumber);
        $eventId2 = $this->eventIdService->generatePurchaseEventId($orderNumber);

        $this->assertEquals("purchase_{$orderNumber}", $eventId1);
        $this->assertEquals($eventId1, $eventId2, 'Deterministic Purchase event ID must be identical on repeated calls');
        $this->assertTrue($this->eventIdService->isValid($eventId1));
    }

    /**
     * 5. Same order produces same Purchase event ID via generateForEvent.
     */
    public function test_same_order_produces_same_purchase_event_id_via_context(): void
    {
        $orderNumber = 'BFB-ORD-5555';
        $id1 = $this->eventIdService->generateForEvent('Purchase', ['order_number' => $orderNumber]);
        $id2 = $this->eventIdService->generateForEvent('Purchase', ['order_id' => $orderNumber]);

        $this->assertEquals("purchase_{$orderNumber}", $id1);
        $this->assertEquals($id1, $id2);
    }

    /**
     * 6. Different orders produce different Purchase event IDs.
     */
    public function test_different_orders_produce_different_purchase_event_ids(): void
    {
        $id1 = $this->eventIdService->generatePurchaseEventId('ORD-1001');
        $id2 = $this->eventIdService->generatePurchaseEventId('ORD-1002');

        $this->assertNotEquals($id1, $id2);
        $this->assertEquals('purchase_ORD-1001', $id1);
        $this->assertEquals('purchase_ORD-1002', $id2);
    }

    /**
     * 7. Purchase event ID contains no PII and rejects PII if mistakenly passed.
     */
    public function test_purchase_event_id_rejects_pii(): void
    {
        // Must reject raw customer email passed as order number
        $this->expectException(\InvalidArgumentException::class);
        $this->eventIdService->generatePurchaseEventId('customer@example.com');
    }

    /**
     * 8. Event IDs are safe/valid according to Meta CAPI specification.
     */
    public function test_event_ids_are_safe_and_within_length_limit(): void
    {
        $id = $this->eventIdService->generatePurchaseEventId('CB-VERY-LONG-ORDER-IDENTIFIER-NUMBER-1234567890-ABCDEF');
        $this->assertLessThanOrEqual(64, strlen($id));
        $this->assertTrue($this->eventIdService->isValid($id));

        // Invalid IDs must be rejected
        $this->assertFalse($this->eventIdService->isValid(''));
        $this->assertFalse($this->eventIdService->isValid(null));
        $this->assertFalse($this->eventIdService->isValid('id with spaces'));
        $this->assertFalse($this->eventIdService->isValid('id<with>html'));
        $this->assertFalse($this->eventIdService->isValid('id@domain.com'));
        $this->assertFalse($this->eventIdService->isValid(str_repeat('a', 65)));
    }

    /**
     * 9. Same event_id + event_name + pixel is detected as duplicate when already sent.
     */
    public function test_same_event_id_and_pixel_detected_as_duplicate(): void
    {
        $pixel = $this->createDynamicTestPixel();
        $eventId = 'dup_test_001';

        // 1. Initial sent record
        MetaTrackingEvent::create([
            'pixel_id'             => $pixel->pixel_id,
            'event_name'           => 'PageView',
            'event_id'             => $eventId,
            'server_status'        => MetaTrackingEvent::STATUS_SENT,
            'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
            'response_code'        => 200,
        ]);

        $isDup = $this->eventIdService->isDuplicate($pixel->pixel_id, 'PageView', $eventId);
        $this->assertTrue($isDup);

        // Different event name on same event ID is NOT duplicate
        $isDifferentEvent = $this->eventIdService->isDuplicate($pixel->pixel_id, 'AddToCart', $eventId);
        $this->assertFalse($isDifferentEvent);
    }

    /**
     * 10. Same event_id on different Pixels is NOT treated as the same event.
     */
    public function test_same_event_id_on_different_pixels_is_not_duplicate(): void
    {
        $pixelA = '990000000000001';
        $pixelB = '990000000000002';
        $eventId = 'shared_event_id_001';

        MetaTrackingEvent::create([
            'pixel_id'      => $pixelA,
            'event_name'    => 'Purchase',
            'event_id'      => $eventId,
            'server_status' => MetaTrackingEvent::STATUS_SENT,
        ]);

        // Pixel A has sent it -> duplicate for Pixel A
        $this->assertTrue($this->eventIdService->isDuplicate($pixelA, 'Purchase', $eventId));

        // Pixel B has NOT sent it -> NOT duplicate for Pixel B
        $this->assertFalse($this->eventIdService->isDuplicate($pixelB, 'Purchase', $eventId));
    }

    /**
     * 11 & 12. Failed server event can be retried with SAME event_id, without generating a new ID.
     */
    public function test_failed_server_event_can_be_retried_with_same_event_id(): void
    {
        $pixel = $this->createDynamicTestPixel();
        $orderNumber = 'CB-RETRY-TEST-01';
        $eventId = $this->eventIdService->generatePurchaseEventId($orderNumber);

        // Use fakeSequence: Attempt 1 returns 500, Attempt 2 returns 200
        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::sequence()
                ->push('Internal Meta Error', 500)
                ->push(['events_received' => 1, 'fbtrace_id' => 'trace_retry_ok'], 200),
        ]);

        // Attempt 1: Fails with 500
        $result1 = $this->apiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
        ]);

        $this->assertFalse($result1['success']);
        $this->assertEquals($eventId, $result1['event_id']);

        $record1 = MetaTrackingEvent::forPixelAndEvent($pixel->pixel_id, 'Purchase', $eventId)->first();
        $this->assertNotNull($record1);
        $this->assertEquals(MetaTrackingEvent::STATUS_FAILED, $record1->server_status);

        // Attempt 2: Retry succeeds with 200 using SAME event_id
        $result2 = $this->apiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
        ]);

        $this->assertTrue($result2['success']);
        $this->assertEquals($eventId, $result2['event_id']);

        // Database record must be updated to 'sent' with the EXACT same event_id
        $record2 = MetaTrackingEvent::forPixelAndEvent($pixel->pixel_id, 'Purchase', $eventId)->first();
        $this->assertEquals(MetaTrackingEvent::STATUS_SENT, $record2->server_status);
        $this->assertEquals($eventId, $record2->event_id);

        // Assert total records in table for this event is exactly 1 (not duplicated)
        $count = MetaTrackingEvent::where('pixel_id', $pixel->pixel_id)
            ->where('event_name', 'Purchase')
            ->where('event_id', $eventId)
            ->count();
        $this->assertEquals(1, $count);
    }

    /**
     * 13. Duplicate Purchase is idempotently blocked from being sent to Meta twice.
     */
    public function test_duplicate_purchase_is_idempotently_blocked(): void
    {
        $pixel = $this->createDynamicTestPixel();
        $orderNumber = 'CB-IDEMP-999';
        $eventId = $this->eventIdService->generatePurchaseEventId($orderNumber);

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_first_send',
            ], 200),
        ]);

        // First attempt: sent successfully
        $res1 = $this->apiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
        ]);
        $this->assertTrue($res1['success']);
        $this->assertFalse($res1['is_duplicate'] ?? false);

        // Second attempt with exact same event_id
        $res2 = $this->apiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
        ]);

        // Duplicate must be blocked idempotently
        $this->assertTrue($res2['success']);
        $this->assertTrue($res2['is_duplicate']);
        $this->assertTrue($res2['skipped']);
        $this->assertEquals($eventId, $res2['event_id']);
        $this->assertStringContainsString('Duplicate event', $res2['error_message']);

        // Assert Meta was called ONLY ONCE
        Http::assertSentCount(1);
    }

    /**
     * 14. MetaConversionApiService preserves supplied event_id without alteration.
     */
    public function test_api_service_preserves_supplied_event_id(): void
    {
        $pixel = $this->createDynamicTestPixel();
        $customEventId = 'custom_exact_id_12345';

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response(['events_received' => 1], 200),
        ]);

        $result = $this->apiService->sendEvent([
            'event_name' => 'AddToCart',
            'event_id'   => $customEventId,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals($customEventId, $result['event_id']);

        Http::assertSent(function ($request) use ($customEventId) {
            return ($request->data()['data'][0]['event_id'] ?? null) === $customEventId;
        });
    }

    /**
     * 15. Invalid event_id is rejected gracefully.
     */
    public function test_invalid_event_id_is_rejected(): void
    {
        $this->createDynamicTestPixel();

        Http::fake();

        $result = $this->apiService->sendEvent([
            'event_name' => 'PageView',
            'event_id'   => 'invalid id with spaces and <chars>',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid event_id format', $result['error_message']);
        Http::assertNothingSent();
    }

    /**
     * 16. No secrets appear in event IDs.
     */
    public function test_no_secrets_appear_in_event_ids(): void
    {
        $id = $this->eventIdService->generateForEvent('PageView');
        $this->assertStringNotContainsString('EAAG', $id);
        $this->assertStringNotContainsString('token', $id);
        $this->assertStringNotContainsString('secret', $id);
    }

    /**
     * 17. Existing MetaTrackingEvent records remain compatible.
     */
    public function test_existing_meta_tracking_event_records_remain_compatible(): void
    {
        $pixel = $this->createDynamicTestPixel();

        $legacy = MetaTrackingEvent::create([
            'event_id'      => 'legacy_compat_001',
            'event_name'    => 'PageView',
            'pixel_id'      => $pixel->pixel_id,
            'server_status' => MetaTrackingEvent::STATUS_SENT,
        ]);

        $this->assertTrue($legacy->isServerSent());
        $this->assertEquals('legacy_compat_001', $legacy->event_id);
    }
}
