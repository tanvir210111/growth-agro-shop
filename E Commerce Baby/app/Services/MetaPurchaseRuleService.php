<?php

namespace App\Services;

use App\Models\MetaPurchaseRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MetaPurchaseRuleService — Phase 9 Customer/Order History Rule Engine
 *
 * Evaluates active purchase rules against computed customer order-history metrics
 * to determine the appropriate Meta CAPI Purchase dispatch mode (instant/delay/hold).
 *
 * CRITICAL PRINCIPLES:
 *   - This service ONLY controls Meta Purchase dispatch timing.
 *   - It NEVER rejects orders, cancels orders, or modifies any order/payment data.
 *   - All evaluation is FAIL-OPEN: any exception falls back to global setting silently.
 *   - No fraud API, no courier API, no IP/device reputation.
 *   - Customer history is PHONE-BASED. Current order is ALWAYS excluded.
 *
 * ==========================================================================
 * APPROVED CONDITION FIELDS (canonical names):
 * ==========================================================================
 *   customer_order_count      — COUNT of all previous orders for this phone
 *   customer_delivered_count  — COUNT of previous orders with status 'delivered'
 *   customer_return_count     — COUNT of previous orders with status 'returned'/'return'
 *   customer_cancelled_count  — COUNT of previous orders with status 'cancelled'/'cancel'/'rejected'
 *   customer_completed_count  — Same as customer_delivered_count (delivered = completed in this project)
 *   customer_return_ratio     — return_count / order_count  (0 if order_count = 0)
 *   customer_has_previous_order — 1 if order_count > 0, else 0
 *   order_source              — Current order source string: 'LANDING' or 'MAIN_WEBSITE'
 *   order_total               — Current order's total_amount (float, BDT)
 *
 * ==========================================================================
 * APPROVED OPERATORS:
 * ==========================================================================
 *   =   !=   >   >=   <   <=
 *
 * "between" is NOT supported. Fraud fields are NOT supported.
 *
 * ==========================================================================
 * ORDER STATUS STRINGS (from project codebase — AdminAnalyticsController.php):
 * ==========================================================================
 *   Delivered / Completed  : 'delivered'
 *   Returned               : 'returned', 'return'
 *   Cancelled              : 'cancelled', 'cancel', 'rejected'
 *   Others (not counted)   : 'pending', 'processing', 'shipped', etc.
 */
class MetaPurchaseRuleService
{
    /**
     * Canonical condition field names.
     * Backend whitelist — frontend CANNOT submit arbitrary field names.
     */
    public const ALLOWED_FIELDS = [
        'customer_order_count',
        'customer_delivered_count',
        'customer_return_count',
        'customer_cancelled_count',
        'customer_completed_count',
        'customer_return_ratio',
        'customer_has_previous_order',
        'order_source',
        'order_total',
    ];

    /**
     * Approved operators only. "between" is NOT in scope for Phase 9.
     */
    public const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];

    /**
     * Status strings — verified from AdminAnalyticsController.php and FraudDetectionService.php.
     */
    private const DELIVERED_STATUSES  = ['delivered'];
    private const RETURNED_STATUSES   = ['returned', 'return'];
    private const CANCELLED_STATUSES  = ['cancelled', 'cancel', 'rejected'];

    /**
     * Evaluate active rules (in priority order) for the given purchase context.
     *
     * Returns the first matching rule's result, or null if no rule matches.
     * null = caller uses the global Phase 8 setting (fail-open).
     *
     * @param  string      $phone           Normalized customer phone (e.g. 01XXXXXXXXX)
     * @param  float       $orderTotal      Current order's total_amount (BDT)
     * @param  string      $orderSource     'LANDING' or 'MAIN_WEBSITE'
     * @param  string|null $excludeInvoice  Current order's invoice_no (excluded from history)
     * @return array|null  ['mode','delay_minutes','rule_id','rule_name'] or null
     */
    public function evaluate(
        string $phone,
        float  $orderTotal    = 0.0,
        string $orderSource   = 'MAIN_WEBSITE',
        ?string $excludeInvoice = null
    ): ?array {
        try {
            // Load active rules in priority order (lower priority number = evaluated first)
            // Tie-break: lower id wins (deterministic for same-priority rules)
            $rules = MetaPurchaseRule::where('is_active', true)
                ->orderBy('priority', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($rules->isEmpty()) {
                return null;
            }

            $metrics = $this->computeMetrics($phone, $orderTotal, $orderSource, $excludeInvoice);

            foreach ($rules as $rule) {
                if ($this->matchesRule($metrics, $rule)) {
                    return [
                        'mode'          => $rule->action_mode,
                        'delay_minutes' => (int) ($rule->delay_minutes ?? 0),
                        'rule_id'       => (int) $rule->id,
                        'rule_name'     => (string) $rule->rule_name,
                    ];
                }
            }

            return null; // No rule matched — use global fallback

        } catch (\Throwable $e) {
            Log::warning('[MetaPurchaseRuleService] evaluate() error (fail-open): ' . $e->getMessage(), [
                'phone_hash' => substr(md5($phone), 0, 8), // No raw phone in logs
            ]);
            return null;
        }
    }

    /**
     * Compute all 9 canonical metrics from the orders table for a given phone.
     * STRICTLY READ-ONLY. Current order is excluded.
     * Division by zero is guarded.
     *
     * @param  string      $phone
     * @param  float       $orderTotal    Current order total
     * @param  string      $orderSource   'LANDING' or 'MAIN_WEBSITE'
     * @param  string|null $excludeInvoice  Current order's invoice_no
     * @return array<string,int|float|string>
     */
    public function computeMetrics(
        string  $phone,
        float   $orderTotal   = 0.0,
        string  $orderSource  = 'MAIN_WEBSITE',
        ?string $excludeInvoice = null
    ): array {
        try {
            $normalizedPhone = $this->normalizePhone($phone);

            $query = DB::table('orders')
                ->where('customer_phone', $normalizedPhone)
                ->select(['status', 'total_amount']);

            // ALWAYS exclude the current order from history
            if (!empty($excludeInvoice)) {
                $query->where('invoice_no', '!=', $excludeInvoice);
            }

            $rows = $query->get();

            $orderCount     = $rows->count();
            $deliveredCount = $rows->filter(fn($r) => strtolower((string)$r->status) === 'delivered')->count();
            $returnCount    = $rows->filter(fn($r) => in_array(strtolower((string)$r->status), self::RETURNED_STATUSES, true))->count();
            $cancelledCount = $rows->filter(fn($r) => in_array(strtolower((string)$r->status), self::CANCELLED_STATUSES, true))->count();

            // customer_completed_count ≡ customer_delivered_count (delivered = completed in this project)
            $completedCount = $deliveredCount;

            // return_ratio = return_count / order_count; guard division by zero
            $returnRatio = ($orderCount > 0) ? round($returnCount / $orderCount, 4) : 0.0;

            $hasPrevious = ($orderCount > 0) ? 1 : 0;

            return [
                'customer_order_count'        => $orderCount,
                'customer_delivered_count'    => $deliveredCount,
                'customer_return_count'       => $returnCount,
                'customer_cancelled_count'    => $cancelledCount,
                'customer_completed_count'    => $completedCount,
                'customer_return_ratio'       => $returnRatio,
                'customer_has_previous_order' => $hasPrevious,
                'order_source'                => $orderSource,
                'order_total'                 => $orderTotal,
            ];

        } catch (\Throwable $e) {
            Log::warning('[MetaPurchaseRuleService] computeMetrics() error (fail-open): ' . $e->getMessage(), [
                'phone_hash' => substr(md5($phone), 0, 8),
            ]);

            // Safe zero-history defaults — fail-open
            return [
                'customer_order_count'        => 0,
                'customer_delivered_count'    => 0,
                'customer_return_count'       => 0,
                'customer_cancelled_count'    => 0,
                'customer_completed_count'    => 0,
                'customer_return_ratio'       => 0.0,
                'customer_has_previous_order' => 0,
                'order_source'                => $orderSource,
                'order_total'                 => $orderTotal,
            ];
        }
    }

    /**
     * Test whether a set of metrics satisfies a single rule's condition.
     * Enforces whitelist on field and operator before any comparison.
     */
    protected function matchesRule(array $metrics, MetaPurchaseRule $rule): bool
    {
        $field    = $rule->condition_field;
        $operator = $rule->operator;

        // Whitelist enforcement — backend safety gate
        if (!in_array($field, self::ALLOWED_FIELDS, true)) {
            Log::warning("[MetaPurchaseRuleService] Rule #{$rule->id} has invalid condition_field '{$field}' — skipped.");
            return false;
        }
        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            Log::warning("[MetaPurchaseRuleService] Rule #{$rule->id} has invalid operator '{$operator}' — skipped.");
            return false;
        }

        $metricValue = $metrics[$field] ?? null;
        if ($metricValue === null) {
            return false;
        }

        $conditionValue = $rule->condition_value;

        // String comparison for order_source; numeric for all others
        if ($field === 'order_source') {
            return $this->compareString((string) $metricValue, $operator, (string) $conditionValue);
        }

        return $this->compareNumeric((float) $metricValue, $operator, (float) $conditionValue);
    }

    /**
     * Numeric comparison for int/float metrics.
     */
    protected function compareNumeric(float $metricValue, string $operator, float $conditionValue): bool
    {
        return match ($operator) {
            '='  => $metricValue == $conditionValue,
            '!=' => $metricValue != $conditionValue,
            '>'  => $metricValue >  $conditionValue,
            '>=' => $metricValue >= $conditionValue,
            '<'  => $metricValue <  $conditionValue,
            '<=' => $metricValue <= $conditionValue,
            default => false,
        };
    }

    /**
     * String comparison (case-insensitive) for order_source field.
     */
    protected function compareString(string $metricValue, string $operator, string $conditionValue): bool
    {
        $a = strtoupper(trim($metricValue));
        $b = strtoupper(trim($conditionValue));

        return match ($operator) {
            '='  => $a === $b,
            '!=' => $a !== $b,
            default => false, // >, >=, <, <= not meaningful for strings
        };
    }

    /**
     * Normalize a BD phone number to canonical 11-digit local format.
     * Strips +880 / 880 country code prefix.
     * Raw phone is never logged.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Strip BD country code: +8801XXXXXXXXX (13 digits) → 01XXXXXXXXX (11 digits)
        if (strlen($phone) === 13 && str_starts_with($phone, '880')) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }

    /**
     * Validate a rule definition array.
     * Returns array of human-readable error strings (empty = valid).
     * Called by AdminMetaTrackingController for both create and update.
     */
    public static function validateRuleData(array $data): array
    {
        $errors = [];

        // Rule name
        if (empty($data['rule_name']) || strlen(trim((string) $data['rule_name'])) < 2) {
            $errors[] = 'rule_name is required (minimum 2 characters).';
        } elseif (strlen(trim((string) $data['rule_name'])) > 255) {
            $errors[] = 'rule_name must not exceed 255 characters.';
        }

        // Condition field — whitelist enforced here (cannot be bypassed by frontend)
        if (!isset($data['condition_field']) || !in_array($data['condition_field'], self::ALLOWED_FIELDS, true)) {
            $errors[] = 'condition_field must be one of: ' . implode(', ', self::ALLOWED_FIELDS) . '.';
        }

        // Operator — whitelist enforced here
        if (!isset($data['operator']) || !in_array($data['operator'], self::ALLOWED_OPERATORS, true)) {
            $errors[] = 'operator must be one of: ' . implode(', ', self::ALLOWED_OPERATORS) . '.';
        }

        // Condition value
        if (!isset($data['condition_value']) || $data['condition_value'] === '' || $data['condition_value'] === null) {
            $errors[] = 'condition_value is required.';
        } elseif (
            isset($data['condition_field']) &&
            $data['condition_field'] === 'order_source'
        ) {
            // order_source: must be one of the allowed values
            $allowedSources = ['LANDING', 'MAIN_WEBSITE'];
            if (!in_array(strtoupper(trim((string) $data['condition_value'])), $allowedSources, true)) {
                $errors[] = 'For order_source, condition_value must be LANDING or MAIN_WEBSITE.';
            }
        } elseif (!is_numeric($data['condition_value'])) {
            // All other fields require numeric values
            if (!isset($data['condition_field']) || $data['condition_field'] !== 'order_source') {
                $errors[] = 'condition_value must be numeric for field: ' . ($data['condition_field'] ?? '(unknown)') . '.';
            }
        }

        // Action mode
        if (!isset($data['action_mode']) || !in_array($data['action_mode'], ['instant', 'delay', 'hold'], true)) {
            $errors[] = 'action_mode must be one of: instant, delay, hold.';
        }

        // Delay minutes
        if (isset($data['delay_minutes'])) {
            $dm = (int) $data['delay_minutes'];
            if ($dm < 0 || $dm > 1440) {
                $errors[] = 'delay_minutes must be between 0 and 1440.';
            }
        }

        // Priority
        if (isset($data['priority'])) {
            if (!is_numeric($data['priority']) || (int) $data['priority'] < 0) {
                $errors[] = 'priority must be a non-negative integer.';
            }
        }

        return $errors;
    }
}
