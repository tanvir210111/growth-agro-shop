<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaPurchaseControlService
{
    protected MetaTrackingConfigService $configService;
    protected MetaConversionApiService $capiService;
    protected MetaEventIdService $eventIdService;
    protected MetaCapiUserDataService $userDataService;
    protected MetaPurchaseRuleService $ruleService;

    public const MAX_RETRY_ATTEMPTS = 5;

    public function __construct(
        MetaTrackingConfigService $configService,
        MetaConversionApiService $capiService,
        MetaEventIdService $eventIdService,
        MetaCapiUserDataService $userDataService,
        MetaPurchaseRuleService $ruleService
    ) {
        $this->configService = $configService;
        $this->capiService = $capiService;
        $this->eventIdService = $eventIdService;
        $this->userDataService = $userDataService;
        $this->ruleService = $ruleService;
    }

    /**
     * Handle Server-Side Purchase event for a newly created Main Website Order.
     * Respects configured purchase_event_mode: instant, delay, or hold.
     * Guaranteed fail-open: errors will never interrupt or rollback the customer order.
     */
    public function handleMainWebsiteOrder(
        Order $order,
        array $orderData,
        array $items,
        float $total,
        float $shipping,
        ?Request $request = null
    ): array {
        try {
            if (!$this->configService->isTrackingEnabled()) {
                return ['success' => false, 'skipped' => true, 'reason' => 'Tracking globally disabled'];
            }

            if (!$this->configService->isServerEventToggleEnabled('purchase')) {
                return ['success' => false, 'skipped' => true, 'reason' => 'Server Purchase event toggle disabled'];
            }

            $activePixel = $this->configService->getActivePixel();
            if (!$activePixel) {
                return ['success' => false, 'skipped' => true, 'reason' => 'No active Meta Pixel configured'];
            }

            $orderNumber = $order->invoice_no;
            $purchaseEventId = $this->eventIdService->generatePurchaseEventId($orderNumber);

            // Phase 9: Evaluate customer history rules if auto_rules_enabled is on.
            // Rule result ONLY controls dispatch timing — never affects order creation.
            $ruleResult = null;
            if ($this->configService->isAutoRulesEnabled()) {
                $customerPhone = $orderData['customer_phone'] ?? $orderData['phone'] ?? $order->customer_phone ?? '';
                $ruleResult = $this->ruleService->evaluate(
                    (string) $customerPhone,
                    (float) $total,
                    'MAIN_WEBSITE',   // order_source for main website orders
                    $orderNumber      // exclude current order from history
                );
            }

            // Resolve final mode: rule result takes priority, then global setting
            $purchaseMode    = $ruleResult['mode'] ?? $this->configService->getPurchaseEventMode();
            $delayMinutes    = ($ruleResult && isset($ruleResult['delay_minutes']) && $ruleResult['delay_minutes'] > 0)
                ? $ruleResult['delay_minutes']
                : $this->configService->getPurchaseDelayMinutes();
            $appliedRuleId   = $ruleResult['rule_id'] ?? null;
            $appliedRuleName = $ruleResult['rule_name'] ?? null;

            // Build standard CAPI User Data and Custom Data
            $userData = $this->userDataService->fromOrder($orderData, $request);
            $customData = [
                'currency'     => 'BDT',
                'value'        => round($total, 2),
                'content_type' => 'product',
                'content_ids'  => array_values(array_filter(array_map(function ($it) {
                    return (string) ($it['slug'] ?? $it['product_id'] ?? $it['title'] ?? '');
                }, $items))),
                'contents'     => array_map(function ($it) {
                    return [
                        'id'         => (string) ($it['product_id'] ?? $it['title'] ?? 'item'),
                        'quantity'   => (int) ($it['quantity'] ?? 1),
                        'item_price' => (float) ($it['price'] ?? 0),
                    ];
                }, $items),
                'num_items'    => array_sum(array_column($items, 'quantity')) ?: 1,
                'order_id'     => $orderNumber,
            ];

            $eventSourceUrl = $request ? $request->fullUrl() : url("/order/success/{$orderNumber}");

            // 1. INSTANT MODE: Dispatch immediately, persist sent record with rule snapshot
            if ($purchaseMode === 'instant') {
                $result = $this->capiService->sendEvent([
                    'event_name'       => 'Purchase',
                    'event_id'         => $purchaseEventId,
                    'order_id'         => $orderNumber,
                    'order_source'     => 'MAIN_WEBSITE',
                    'pixel_id'         => $activePixel->pixel_id,
                    'event_source_url' => $eventSourceUrl,
                    'user_data'        => $userData,
                    'custom_data'      => $customData,
                ]);

                // Persist event record with rule snapshot (even for instant — snapshot immutability)
                MetaTrackingEvent::updateOrCreate(
                    [
                        'pixel_id'   => $activePixel->pixel_id,
                        'event_name' => 'Purchase',
                        'event_id'   => $purchaseEventId,
                    ],
                    [
                        'order_id'             => $orderNumber,
                        'order_source'         => 'MAIN_WEBSITE',
                        'action_source'        => 'website',
                        'event_source_url'     => $eventSourceUrl,
                        'user_data'            => $userData,
                        'custom_data'          => $customData,
                        'browser_status'       => MetaTrackingEvent::STATUS_PENDING,
                        'server_status'        => ($result['success'] ?? false) ? MetaTrackingEvent::STATUS_SENT : 'failed',
                        'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
                        'purchase_mode'        => 'instant',
                        'hold_reason'          => null,
                        'rule_id'              => $appliedRuleId,
                        'rule_name'            => $appliedRuleName,
                        'scheduled_at'         => null,
                        'sent_at'              => ($result['success'] ?? false) ? now() : null,
                        'attempt_count'        => 1,
                    ]
                );

                return $result;
            }

            // 2. DELAY MODE: Persist scheduled event record
            if ($purchaseMode === 'delay') {
                $scheduledAt = now()->addMinutes($delayMinutes);

                $event = MetaTrackingEvent::updateOrCreate(
                    [
                        'pixel_id'   => $activePixel->pixel_id,
                        'event_name' => 'Purchase',
                        'event_id'   => $purchaseEventId,
                    ],
                    [
                        'order_id'             => $orderNumber,
                        'order_source'         => 'MAIN_WEBSITE',
                        'action_source'        => 'website',
                        'event_source_url'     => $eventSourceUrl,
                        'user_data'            => $userData,
                        'custom_data'          => $customData,
                        'browser_status'       => MetaTrackingEvent::STATUS_PENDING,
                        'server_status'        => 'scheduled',
                        'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
                        'purchase_mode'        => 'delay',
                        'hold_reason'          => $appliedRuleName ? "Rule: {$appliedRuleName}" : null,
                        'rule_id'              => $appliedRuleId,
                        'rule_name'            => $appliedRuleName,
                        'scheduled_at'         => $scheduledAt,
                        'sent_at'              => null,
                        'attempt_count'        => 0,
                    ]
                );

                return [
                    'success'      => true,
                    'delayed'      => true,
                    'event_id'     => $purchaseEventId,
                    'scheduled_at' => $scheduledAt->toIso8601String(),
                ];
            }

            // 3. HOLD MODE: Persist held event record
            if ($purchaseMode === 'hold') {
                $event = MetaTrackingEvent::updateOrCreate(
                    [
                        'pixel_id'   => $activePixel->pixel_id,
                        'event_name' => 'Purchase',
                        'event_id'   => $purchaseEventId,
                    ],
                    [
                        'order_id'             => $orderNumber,
                        'order_source'         => 'MAIN_WEBSITE',
                        'action_source'        => 'website',
                        'event_source_url'     => $eventSourceUrl,
                        'user_data'            => $userData,
                        'custom_data'          => $customData,
                        'browser_status'       => MetaTrackingEvent::STATUS_PENDING,
                        'server_status'        => MetaTrackingEvent::STATUS_HELD,
                        'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
                        'purchase_mode'        => 'hold',
                        'hold_reason'          => $appliedRuleName ? "Rule: {$appliedRuleName}" : 'Admin Hold',
                        'rule_id'              => $appliedRuleId,
                        'rule_name'            => $appliedRuleName,
                        'scheduled_at'         => null,
                        'sent_at'              => null,
                        'attempt_count'        => 0,
                    ]
                );

                return [
                    'success'  => true,
                    'held'     => true,
                    'event_id' => $purchaseEventId,
                ];
            }

            return ['success' => false, 'error' => "Unknown purchase mode: {$purchaseMode}"];
        } catch (\Throwable $e) {
            Log::warning('[MetaPurchaseControl] Main website purchase control fail-open: ' . MetaTrackingEvent::scrubSecrets($e->getMessage()));
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle Server-Side Purchase event coordination when a Landing Page order syncs to Laravel.
     * If mode was delay/hold, creates/updates the single authoritative record in Laravel meta_tracking_events.
     */
    public function handleLandingOrderSync(Order $order, Request $request): array
    {
        try {
            if (!$this->configService->isTrackingEnabled()) {
                return ['success' => false, 'skipped' => true, 'reason' => 'Tracking globally disabled'];
            }

            if (!$this->configService->isServerEventToggleEnabled('purchase')) {
                return ['success' => false, 'skipped' => true, 'reason' => 'Server Purchase event toggle disabled'];
            }

            $activePixel = $this->configService->getActivePixel();
            if (!$activePixel) {
                return ['success' => false, 'skipped' => true, 'reason' => 'No active Meta Pixel configured'];
            }

            $orderNumber = $order->invoice_no;
            $purchaseEventId = $this->eventIdService->generatePurchaseEventId($orderNumber);

            // Phase 9: Evaluate customer history rules if auto_rules_enabled is on.
            $ruleResult = null;
            if ($this->configService->isAutoRulesEnabled()) {
                $customerPhone = $order->customer_phone ?? '';
                $ruleResult = $this->ruleService->evaluate(
                    (string) $customerPhone,
                    (float) $order->total_amount,
                    'LANDING',       // order_source for landing page orders
                    $orderNumber     // exclude current order from history
                );
            }

            $purchaseMode    = $ruleResult['mode'] ?? $this->configService->getPurchaseEventMode();
            $delayMinutes    = ($ruleResult && isset($ruleResult['delay_minutes']) && $ruleResult['delay_minutes'] > 0)
                ? $ruleResult['delay_minutes']
                : $this->configService->getPurchaseDelayMinutes();
            $appliedRuleId   = $ruleResult['rule_id'] ?? null;
            $appliedRuleName = $ruleResult['rule_name'] ?? null;

            // Extract customer info from synced order
            $userData = $this->userDataService->fromOrder([
                'phone'         => $order->customer_phone,
                'customer_name' => $order->customer_name,
                'name'          => $order->customer_name,
                'city'          => $order->city_type === 'inside_dhaka' ? 'Dhaka' : null,
                'country'       => 'bd',
                'external_id'   => $orderNumber,
            ], $request);

            $customData = [
                'currency'  => 'BDT',
                'value'     => (float) $order->total_amount,
                'num_items' => 1,
                'order_id'  => $orderNumber,
            ];

            $eventSourceUrl = $request ? $request->fullUrl() : url("/product/chicken-booster/success/{$orderNumber}");

            // 1. INSTANT: Node dispatches instant directly; if not dispatched yet, record as instant
            if ($purchaseMode === 'instant') {
                $existing = MetaTrackingEvent::where('pixel_id', $activePixel->pixel_id)
                    ->where('event_name', 'Purchase')
                    ->where('event_id', $purchaseEventId)
                    ->first();

                if (!$existing) {
                    MetaTrackingEvent::create([
                        'pixel_id'             => $activePixel->pixel_id,
                        'event_name'           => 'Purchase',
                        'event_id'             => $purchaseEventId,
                        'order_id'             => $orderNumber,
                        'order_source'         => 'LANDING',
                        'action_source'        => 'website',
                        'event_source_url'     => $eventSourceUrl,
                        'user_data'            => $userData,
                        'custom_data'          => $customData,
                        'browser_status'       => MetaTrackingEvent::STATUS_PENDING,
                        'server_status'        => MetaTrackingEvent::STATUS_SENT,
                        'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
                        'purchase_mode'        => 'instant',
                        'hold_reason'          => null,
                        'rule_id'              => $appliedRuleId,
                        'rule_name'            => $appliedRuleName,
                        'sent_at'              => now(),
                        'attempt_count'        => 1,
                    ]);
                }

                return ['success' => true, 'mode' => 'instant'];
            }

            // 2. DELAY: Persist scheduled event in Laravel authoritative queue
            if ($purchaseMode === 'delay') {
                $scheduledAt = now()->addMinutes($delayMinutes);

                MetaTrackingEvent::updateOrCreate(
                    [
                        'pixel_id'   => $activePixel->pixel_id,
                        'event_name' => 'Purchase',
                        'event_id'   => $purchaseEventId,
                    ],
                    [
                        'order_id'             => $orderNumber,
                        'order_source'         => 'LANDING',
                        'action_source'        => 'website',
                        'event_source_url'     => $eventSourceUrl,
                        'user_data'            => $userData,
                        'custom_data'          => $customData,
                        'browser_status'       => MetaTrackingEvent::STATUS_PENDING,
                        'server_status'        => 'scheduled',
                        'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
                        'purchase_mode'        => 'delay',
                        'hold_reason'          => $appliedRuleName ? "Rule: {$appliedRuleName}" : null,
                        'rule_id'              => $appliedRuleId,
                        'rule_name'            => $appliedRuleName,
                        'scheduled_at'         => $scheduledAt,
                        'sent_at'              => null,
                        'attempt_count'        => 0,
                    ]
                );

                return ['success' => true, 'delayed' => true, 'scheduled_at' => $scheduledAt->toIso8601String()];
            }

            // 3. HOLD: Persist held event in Laravel authoritative queue
            if ($purchaseMode === 'hold') {
                MetaTrackingEvent::updateOrCreate(
                    [
                        'pixel_id'   => $activePixel->pixel_id,
                        'event_name' => 'Purchase',
                        'event_id'   => $purchaseEventId,
                    ],
                    [
                        'order_id'             => $orderNumber,
                        'order_source'         => 'LANDING',
                        'action_source'        => 'website',
                        'event_source_url'     => $eventSourceUrl,
                        'user_data'            => $userData,
                        'custom_data'          => $customData,
                        'browser_status'       => MetaTrackingEvent::STATUS_PENDING,
                        'server_status'        => MetaTrackingEvent::STATUS_HELD,
                        'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
                        'purchase_mode'        => 'hold',
                        'hold_reason'          => $appliedRuleName ? "Rule: {$appliedRuleName}" : 'Admin Hold',
                        'rule_id'              => $appliedRuleId,
                        'rule_name'            => $appliedRuleName,
                        'scheduled_at'         => null,
                        'sent_at'              => null,
                        'attempt_count'        => 0,
                    ]
                );

                return ['success' => true, 'held' => true];
            }

            return ['success' => false, 'error' => "Unknown purchase mode: {$purchaseMode}"];
        } catch (\Throwable $e) {
            Log::warning('[MetaPurchaseControl] Landing sync purchase control fail-open: ' . MetaTrackingEvent::scrubSecrets($e->getMessage()));
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Release a held Purchase event and dispatch to Meta CAPI.
     * Preserves original Pixel ID and original event_id.
     */
    public function releaseHeldPurchase(int $eventId, ?Admin $admin = null): array
    {
        $event = MetaTrackingEvent::find($eventId);
        if (!$event) {
            return ['success' => false, 'status' => 404, 'message' => 'Tracking event not found.'];
        }

        if ($event->event_name !== 'Purchase') {
            return ['success' => false, 'status' => 422, 'message' => 'Only Purchase events can be released.'];
        }

        if ($event->isServerSent()) {
            return ['success' => false, 'status' => 422, 'message' => 'Event has already been sent to Meta.'];
        }

        if (!$event->isHeld()) {
            return ['success' => false, 'status' => 422, 'message' => 'Event is not currently on hold.'];
        }

        // Verify Pixel exists and token is configured
        $pixel = MetaPixel::where('pixel_id', $event->pixel_id)->first();
        if (!$pixel || !$pixel->has_token) {
            return ['success' => false, 'status' => 422, 'message' => 'Pixel or CAPI token for this event is not configured.'];
        }

        // Dispatch with original event_id and original pixel_id
        $result = $this->capiService->sendEvent([
            'event_name'       => 'Purchase',
            'event_id'         => $event->event_id,
            'order_id'         => $event->order_id,
            'pixel_id'         => $event->pixel_id,
            'event_source_url' => $event->event_source_url ?: url("/order/success/{$event->order_id}"),
            'user_data'        => $event->user_data ?: [],
            'custom_data'      => $event->custom_data ?: [],
        ]);

        $event->refresh();

        if ($result['success']) {
            $event->update([
                'server_status'   => MetaTrackingEvent::STATUS_SENT,
                'sent_at'         => now(),
                'released_at'     => now(),
                'released_by'     => $admin?->id,
                'error_message'   => null,
                'response_code'   => $result['http_status'] ?? 200,
            ]);

            return [
                'success' => true,
                'status'  => 200,
                'message' => 'Purchase event released and successfully sent to Meta.',
                'event'   => $event->fresh()->toSafeArray(),
            ];
        }

        // On failure: keep held without marking sent
        $event->update([
            'server_status'   => MetaTrackingEvent::STATUS_HELD,
            'error_message'   => $result['error_message'] ?? 'Meta dispatch failed',
            'response_code'   => $result['http_status'] ?? null,
        ]);

        return [
            'success' => false,
            'status'  => 502,
            'message' => 'Failed to send event to Meta: ' . ($result['error_message'] ?? 'Unknown error'),
            'event'   => $event->fresh()->toSafeArray(),
        ];
    }

    /**
     * Retry a failed or due Purchase event.
     * Enforces bounded retries, same event_id, and same pixel_id.
     */
    public function retryPurchaseEvent(int $eventId, ?Admin $admin = null, int $maxAttempts = self::MAX_RETRY_ATTEMPTS): array
    {
        $event = MetaTrackingEvent::find($eventId);
        if (!$event) {
            return ['success' => false, 'status' => 404, 'message' => 'Tracking event not found.'];
        }

        if ($event->event_name !== 'Purchase') {
            return ['success' => false, 'status' => 422, 'message' => 'Only Purchase events can be retried.'];
        }

        if ($event->isServerSent()) {
            return ['success' => false, 'status' => 422, 'message' => 'Event has already been sent successfully.'];
        }

        if (!$event->canRetry($maxAttempts)) {
            return [
                'success' => false,
                'status'  => 422,
                'message' => "Maximum retry attempts ({$maxAttempts}) reached for this event.",
            ];
        }

        $pixel = MetaPixel::where('pixel_id', $event->pixel_id)->first();
        if (!$pixel || !$pixel->has_token) {
            return ['success' => false, 'status' => 422, 'message' => 'Pixel or CAPI token for this event is not configured.'];
        }

        $result = $this->capiService->sendEvent([
            'event_name'       => 'Purchase',
            'event_id'         => $event->event_id,
            'order_id'         => $event->order_id,
            'pixel_id'         => $event->pixel_id,
            'event_source_url' => $event->event_source_url ?: url("/order/success/{$event->order_id}"),
            'user_data'        => $event->user_data ?: [],
            'custom_data'      => $event->custom_data ?: [],
        ]);

        $event->refresh();

        if ($result['success']) {
            $event->update([
                'server_status'   => MetaTrackingEvent::STATUS_SENT,
                'sent_at'         => now(),
                'error_message'   => null,
                'response_code'   => $result['http_status'] ?? 200,
            ]);

            return [
                'success' => true,
                'status'  => 200,
                'message' => 'Purchase event successfully sent to Meta.',
                'event'   => $event->fresh()->toSafeArray(),
            ];
        }

        $isFailedTerminal = ($event->attempt_count >= $maxAttempts);
        $event->update([
            'server_status'   => $isFailedTerminal ? MetaTrackingEvent::STATUS_FAILED : $event->server_status,
            'error_message'   => $result['error_message'] ?? 'Meta dispatch failed',
            'response_code'   => $result['http_status'] ?? null,
        ]);

        return [
            'success' => false,
            'status'  => 502,
            'message' => 'Retry failed: ' . ($result['error_message'] ?? 'Unknown error'),
            'event'   => $event->fresh()->toSafeArray(),
        ];
    }

    /**
     * Process all due delayed Purchase events.
     * Executed automatically by Laravel scheduler (cron) or manually via admin action.
     */
    public function processDueDelayedPurchases(int $limit = 50): array
    {
        $dueEvents = MetaTrackingEvent::where('event_name', 'Purchase')
            ->where('purchase_mode', 'delay')
            ->whereIn('server_status', ['scheduled', 'pending'])
            ->where('scheduled_at', '<=', now())
            ->whereNull('sent_at')
            ->where('attempt_count', '<', self::MAX_RETRY_ATTEMPTS)
            ->orderBy('scheduled_at', 'asc')
            ->limit($limit)
            ->get();

        $processed = 0;
        $succeeded = 0;
        $failed = 0;

        foreach ($dueEvents as $event) {
            $processed++;
            $pixel = MetaPixel::where('pixel_id', $event->pixel_id)->first();
            if (!$pixel || !$pixel->has_token) {
                $failed++;
                $event->update([
                    'error_message'   => 'Pixel or CAPI token not found for delayed event',
                    'last_attempt_at' => now(),
                ]);
                continue;
            }

            $result = $this->capiService->sendEvent([
                'event_name'       => 'Purchase',
                'event_id'         => $event->event_id,
                'order_id'         => $event->order_id,
                'pixel_id'         => $event->pixel_id,
                'event_source_url' => $event->event_source_url ?: url("/order/success/{$event->order_id}"),
                'user_data'        => $event->user_data ?: [],
                'custom_data'      => $event->custom_data ?: [],
            ]);

            $event->refresh();

            if ($result['success']) {
                $succeeded++;
                $event->update([
                    'server_status'   => MetaTrackingEvent::STATUS_SENT,
                    'sent_at'         => now(),
                    'error_message'   => null,
                    'response_code'   => $result['http_status'] ?? 200,
                ]);
            } else {
                $failed++;
                $isTerminal = ($event->attempt_count >= self::MAX_RETRY_ATTEMPTS);
                $event->update([
                    'server_status'   => $isTerminal ? MetaTrackingEvent::STATUS_FAILED : 'scheduled',
                    'error_message'   => $result['error_message'] ?? 'Delayed dispatch failed',
                    'response_code'   => $result['http_status'] ?? null,
                ]);
            }
        }

        return [
            'processed' => $processed,
            'succeeded' => $succeeded,
            'failed'    => $failed,
        ];
    }
}
