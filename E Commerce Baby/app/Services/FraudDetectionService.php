<?php

namespace App\Services;

use App\Models\Order;
use App\Models\TrackingSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FraudDetectionService — Phase 5B Step 2
 *
 * Calculates a deterministic, server-side fraud risk score (0–100) for every
 * newly created order. The result is persisted directly onto the Order record.
 *
 * PRINCIPLES:
 *  - Fail-open: every signal is wrapped; a failure adds 0 points and is logged.
 *  - No auto-rejection: orders are never blocked, cancelled, or modified.
 *  - No PII exposure: phone numbers are masked in logs; API key is never logged.
 *  - No new data collection: only data already captured at checkout is used.
 *  - Idempotent: calling assessOrder() a second time for the same order is safe;
 *    it re-uses the cached courier result from Step 1 (courier_checked_at).
 *
 * SCORE LEVELS:
 *   0  – 29  → LOW
 *   30 – 69  → MEDIUM
 *   70 – 100 → HIGH
 */
class FraudDetectionService
{
    // ------------------------------------------------------------------
    // Score thresholds
    // ------------------------------------------------------------------
    public const LEVEL_LOW_MAX    = 29;
    public const LEVEL_MEDIUM_MAX = 69;

    // ------------------------------------------------------------------
    // Signal point values
    // ------------------------------------------------------------------

    /** BD Courier success rate bands */
    private const COURIER_OK        = 0;   // >= 80 %
    private const COURIER_WARN      = 10;  // 60–79 %
    private const COURIER_BAD       = 20;  // 40–59 %
    private const COURIER_HIGH_RISK = 30;  // <  40 %

    /** Phone velocity (orders in 24 h window, excluding current order) */
    private const VELOCITY_LOW      = 0;   // 0–2
    private const VELOCITY_MED      = 15;  // 3
    private const VELOCITY_HIGH     = 25;  // 4+
    private const VELOCITY_BURST    = 10;  // 2+ within 15 min (additive)

    /** Same-phone cancelled/rejected order history */
    private const CANCEL_ONE        = 10;  // 1 cancelled order
    private const CANCEL_MULTI      = 20;  // 2+ cancelled orders

    /** IP clustering (distinct phone / customer combos from same IP) */
    private const IP_CLUSTER_MED    = 10;  // 3–4 orders in 24 h
    private const IP_CLUSTER_HIGH   = 20;  // 5+ orders in 24 h

    /** Session anomalies */
    private const SESSION_TOO_FAST  = 20;  // checkout < 5 s after session start
    private const SESSION_NO_DEVICE = 10;  // device/browser info missing

    // ------------------------------------------------------------------
    // Status values considered "cancelled / rejected"
    // ------------------------------------------------------------------
    private const CANCELLED_STATUSES = ['cancelled', 'rejected', 'canceled', 'failed', 'refunded'];

    /**
     * @var BdCourierService
     */
    private BdCourierService $courierService;

    public function __construct(BdCourierService $courierService)
    {
        $this->courierService = $courierService;
    }

    // ══════════════════════════════════════════════════════════════════
    // PUBLIC API
    // ══════════════════════════════════════════════════════════════════

    /**
     * Assess a newly-created Order and persist the fraud result onto it.
     *
     * This method is always called AFTER the order has been saved so that:
     *  - courier_checked_at deduplication works correctly
     *  - velocity checks exclude the current order
     *
     * @param  Order  $order  A persisted Order instance (must have ->id)
     * @return array{score:int, level:string, reasons:array, courier:array}
     */
    public function assessOrder(Order $order): array
    {
        $score    = 0;
        $reasons  = [];
        $courier  = [];

        try {
            // ── Signal A: BD Courier delivery-ratio check ──────────────
            [$courierPoints, $courierReasons, $courier] = $this->scoreSignalCourier($order);
            $score  += $courierPoints;
            $reasons = array_merge($reasons, $courierReasons);
        } catch (\Throwable $e) {
            Log::warning('[FraudDetection] Signal A (courier) failed silently.', ['error' => $e->getMessage()]);
        }

        try {
            // ── Signal B: Phone order velocity ─────────────────────────
            [$velocityPoints, $velocityReasons] = $this->scoreSignalVelocity($order);
            $score  += $velocityPoints;
            $reasons = array_merge($reasons, $velocityReasons);
        } catch (\Throwable $e) {
            Log::warning('[FraudDetection] Signal B (velocity) failed silently.', ['error' => $e->getMessage()]);
        }

        try {
            // ── Signal C: Previous cancellation history ─────────────────
            [$cancelPoints, $cancelReasons] = $this->scoreSignalCancellations($order);
            $score  += $cancelPoints;
            $reasons = array_merge($reasons, $cancelReasons);
        } catch (\Throwable $e) {
            Log::warning('[FraudDetection] Signal C (cancellations) failed silently.', ['error' => $e->getMessage()]);
        }

        try {
            // ── Signal D: IP clustering ────────────────────────────────
            [$ipPoints, $ipReasons] = $this->scoreSignalIpClustering($order);
            $score  += $ipPoints;
            $reasons = array_merge($reasons, $ipReasons);
        } catch (\Throwable $e) {
            Log::warning('[FraudDetection] Signal D (IP clustering) failed silently.', ['error' => $e->getMessage()]);
        }

        try {
            // ── Signal E: Session / checkout anomaly ───────────────────
            [$sessionPoints, $sessionReasons] = $this->scoreSignalSessionAnomaly($order);
            $score  += $sessionPoints;
            $reasons = array_merge($reasons, $sessionReasons);
        } catch (\Throwable $e) {
            Log::warning('[FraudDetection] Signal E (session anomaly) failed silently.', ['error' => $e->getMessage()]);
        }

        // ── Cap score to [0, 100] ──────────────────────────────────────
        $score = max(0, min(100, $score));
        $level = $this->scoreToLevel($score);

        // ── Persist result to order ───────────────────────────────────
        try {
            $this->persistResult($order, $score, $level, $reasons, $courier);
        } catch (\Throwable $e) {
            Log::warning('[FraudDetection] Failed to persist fraud result.', ['error' => $e->getMessage()]);
        }

        Log::info('[FraudDetection] Assessment complete.', [
            'order_id'    => $order->id,
            'invoice_no'  => $order->invoice_no,
            'score'       => $score,
            'level'       => $level,
            'reason_count'=> count($reasons),
        ]);

        return [
            'score'   => $score,
            'level'   => $level,
            'reasons' => $reasons,
            'courier' => $courier,
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    // SIGNAL A — BD COURIER DELIVERY RATIO
    // ══════════════════════════════════════════════════════════════════

    /**
     * Signal A: BD Courier delivery ratio.
     * Evaluates cached courier metrics if an admin has previously checked the order.
     * Automatic live courier API checking is disabled to prevent unintended API usage.
     *
     * @return array{int, array, array}  [points, reasons, courierData]
     */
    private function scoreSignalCourier(Order $order): array
    {
        $phone = trim($order->customer_phone ?? '');
        if (empty($phone)) {
            return [0, [], []];
        }

        // Only evaluate Signal A if an admin has explicitly checked this order (courier_checked_at is set)
        if ($order->courier_checked_at) {
            $successRatio     = (float)  ($order->courier_success_rate   ?? 0.0);
            $totalParcels     = (int)    ($order->courier_total_orders    ?? 0);
            $successParcels   = (int)    ($order->courier_delivered       ?? 0);
            $cancelledParcels = (int)    ($order->courier_cancelled       ?? 0);

            $courier = [
                'success'           => true,
                'total_parcels'     => $totalParcels,
                'success_parcels'   => $successParcels,
                'cancelled_parcels' => $cancelledParcels,
                'success_ratio'     => $successRatio,
                'cached'            => true,
            ];

            return $this->evaluateCourierData($successRatio, $totalParcels, $courier);
        }

        // Automatic live check disabled — courier checks only occur on explicit admin action
        return [0, [], []];
    }

    private function evaluateCourierData(float $ratio, int $total, array $courierData): array
    {
        // New customer with no history → no penalty
        if ($total === 0) {
            return [self::COURIER_OK, [], $courierData];
        }

        if ($ratio >= 80.0) {
            return [self::COURIER_OK, [], $courierData];
        }

        if ($ratio >= 60.0) {
            return [
                self::COURIER_WARN,
                [sprintf('BD Courier delivery success rate is %.0f%%', $ratio)],
                $courierData,
            ];
        }

        if ($ratio >= 40.0) {
            return [
                self::COURIER_BAD,
                [sprintf('BD Courier delivery success rate is low (%.0f%%)', $ratio)],
                $courierData,
            ];
        }

        return [
            self::COURIER_HIGH_RISK,
            [sprintf('BD Courier delivery success rate is very low (%.0f%%)', $ratio)],
            $courierData,
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    // SIGNAL B — PHONE ORDER VELOCITY
    // ══════════════════════════════════════════════════════════════════

    /**
     * @return array{int, array}  [points, reasons]
     */
    private function scoreSignalVelocity(Order $order): array
    {
        $phone = trim($order->customer_phone ?? '');
        if (empty($phone)) {
            return [0, []];
        }

        $points  = 0;
        $reasons = [];

        $window24h = Carbon::now()->subHours(24);

        // Exclude the current order itself
        $recentOrders24h = Order::where('customer_phone', $phone)
            ->where('id', '!=', $order->id)
            ->where('created_at', '>=', $window24h)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'created_at']);

        $count24h = $recentOrders24h->count();

        if ($count24h >= 4) {
            $points  += self::VELOCITY_HIGH;
            $reasons[] = sprintf('%d orders placed with same phone in the last 24 hours', $count24h + 1);
        } elseif ($count24h === 3) {
            $points  += self::VELOCITY_MED;
            $reasons[] = 'Multiple orders placed with same phone within 24 hours';
        }
        // 0–2 previous → no penalty

        // Burst: 2+ orders from same phone within 15 minutes
        $window15min = Carbon::now()->subMinutes(15);
        $burstCount  = $recentOrders24h->filter(
            fn($o) => Carbon::parse($o->created_at)->gte($window15min)
        )->count();

        if ($burstCount >= 2) {
            $points  += self::VELOCITY_BURST;
            $reasons[] = 'Multiple orders from same phone within 15 minutes';
        }

        return [$points, $reasons];
    }

    // ══════════════════════════════════════════════════════════════════
    // SIGNAL C — PREVIOUS CANCELLATION HISTORY
    // ══════════════════════════════════════════════════════════════════

    /**
     * @return array{int, array}  [points, reasons]
     */
    private function scoreSignalCancellations(Order $order): array
    {
        $phone = trim($order->customer_phone ?? '');
        if (empty($phone)) {
            return [0, []];
        }

        $cancelledCount = Order::where('customer_phone', $phone)
            ->where('id', '!=', $order->id)
            ->whereIn(DB::raw('LOWER(status)'), self::CANCELLED_STATUSES)
            ->count();

        if ($cancelledCount === 0) {
            return [0, []];
        }

        if ($cancelledCount === 1) {
            return [
                self::CANCEL_ONE,
                ['1 previously cancelled or rejected order from this phone number'],
            ];
        }

        return [
            self::CANCEL_MULTI,
            [sprintf('%d previously cancelled or rejected orders from this phone number', $cancelledCount)],
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    // SIGNAL D — IP CLUSTERING
    // ══════════════════════════════════════════════════════════════════

    /**
     * @return array{int, array}  [points, reasons]
     */
    private function scoreSignalIpClustering(Order $order): array
    {
        $ip = trim($order->ip_address ?? '');
        if (empty($ip)) {
            return [0, []];
        }

        // Skip private/loopback IPs (local dev, internal traffic)
        if (
            str_starts_with($ip, '127.')   ||
            str_starts_with($ip, '192.168.') ||
            str_starts_with($ip, '10.')    ||
            $ip === '::1'
        ) {
            return [0, []];
        }

        $window24h = Carbon::now()->subHours(24);

        // Count orders from this IP with a DIFFERENT phone or customer name
        // (shared household ordering the same product with same phone is normal)
        $clusterCount = Order::where('ip_address', $ip)
            ->where('id', '!=', $order->id)
            ->where('created_at', '>=', $window24h)
            ->where(function ($q) use ($order) {
                $q->where('customer_phone', '!=', $order->customer_phone)
                  ->orWhere('customer_name', '!=', $order->customer_name);
            })
            ->count();

        // Include the current order itself in the total for display
        $totalFromIp = $clusterCount + 1;

        if ($clusterCount >= 4) {
            return [
                self::IP_CLUSTER_HIGH,
                [sprintf('%d orders from the same IP address in 24 hours with different customers', $totalFromIp)],
            ];
        }

        if ($clusterCount >= 2) {
            return [
                self::IP_CLUSTER_MED,
                [sprintf('%d orders from the same IP address with different customers in 24 hours', $totalFromIp)],
            ];
        }

        return [0, []];
    }

    // ══════════════════════════════════════════════════════════════════
    // SIGNAL E — SESSION / CHECKOUT ANOMALY
    // ══════════════════════════════════════════════════════════════════

    /**
     * @return array{int, array}  [points, reasons]
     */
    private function scoreSignalSessionAnomaly(Order $order): array
    {
        $points  = 0;
        $reasons = [];

        // Only evaluate if the order has an associated tracking session
        if (empty($order->session_id)) {
            return [0, []];
        }

        $session = TrackingSession::find($order->session_id);
        if (!$session) {
            return [0, []];
        }

        // ── E1: Checkout completed unusually quickly ───────────────────
        // Compare session_start to the order created_at timestamp
        try {
            $sessionStart = Carbon::parse($session->session_start);
            $orderCreated = Carbon::parse($order->created_at);
            $elapsedSeconds = $sessionStart->diffInSeconds($orderCreated, true);

            // Only flag if we have a measurable, positive difference
            // and it genuinely looks anomalous (< 5 seconds from landing to order)
            if ($elapsedSeconds >= 0 && $elapsedSeconds < 5) {
                $points  += self::SESSION_TOO_FAST;
                $reasons[] = sprintf(
                    'Checkout completed unusually quickly (%d second%s after session start)',
                    $elapsedSeconds,
                    $elapsedSeconds === 1 ? '' : 's'
                );
            }
        } catch (\Throwable $e) {
            // Cannot calculate timing — do not penalize
        }

        // ── E2: Device / browser information missing ───────────────────
        $hasDeviceInfo  = !empty($session->device_type);
        $hasBrowserInfo = !empty($session->browser);

        if (!$hasDeviceInfo && !$hasBrowserInfo) {
            $points  += self::SESSION_NO_DEVICE;
            $reasons[] = 'Incomplete device and browser information for this session';
        }

        return [$points, $reasons];
    }

    // ══════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Map a numeric score to a risk level string.
     */
    private function scoreToLevel(int $score): string
    {
        if ($score <= self::LEVEL_LOW_MAX) {
            return 'LOW';
        }

        if ($score <= self::LEVEL_MEDIUM_MAX) {
            return 'MEDIUM';
        }

        return 'HIGH';
    }

    /**
     * Persist fraud assessment result to the order record.
     * Uses a targeted update so no other order fields are disturbed.
     */
    private function persistResult(Order $order, int $score, string $level, array $reasons, array $courier): void
    {
        $updateData = [
            'fraud_score'  => $score,
            'fraud_level'  => $level,
            'fraud_reasons'=> $reasons, // JSON cast handles serialization
        ];

        // Include courier metrics if they were fetched during this assessment
        // (signal A sets them on $order->* before we get here)
        if ($order->courier_checked_at && !$order->getOriginal('courier_checked_at')) {
            $updateData['courier_success_rate'] = $order->courier_success_rate;
            $updateData['courier_total_orders'] = $order->courier_total_orders;
            $updateData['courier_delivered']    = $order->courier_delivered;
            $updateData['courier_cancelled']    = $order->courier_cancelled;
            $updateData['courier_checked_at']   = $order->courier_checked_at;
        }

        // Direct update is safer than save() to avoid touching other dirty attributes
        Order::where('id', $order->id)->update($updateData);

        // Refresh the in-memory model so callers see the new values
        $order->fraud_score  = $score;
        $order->fraud_level  = $level;
        $order->fraud_reasons = $reasons;
    }

    // ══════════════════════════════════════════════════════════════════
    // UTILITY (accessible by tests)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Expose the level mapping for testing / admin display.
     */
    public function levelForScore(int $score): string
    {
        return $this->scoreToLevel(max(0, min(100, $score)));
    }
}
