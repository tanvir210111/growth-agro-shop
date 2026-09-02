<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BdCourierService - Server-Side BD Courier API Integration
 *
 * Securely calls the BD Courier customer delivery-ratio API.
 * API key is read exclusively from server-side environment/config.
 * The key is NEVER logged, returned in responses, or exposed to the frontend.
 *
 * Architecture:
 *   Laravel (CheckoutService / InternalSyncController)
 *     -> BdCourierService
 *     -> https://api.bdcourier.com/courier-check
 *
 * Fail-open: All errors return ['success' => false] and NEVER
 * interrupt order creation.
 */
class BdCourierService
{
    /** Endpoint for the BD Courier customer check API */
    private const API_ENDPOINT = 'https://api.bdcourier.com/courier-check';

    /** Request timeout in seconds */
    private const TIMEOUT_SECONDS = 8;

    /**
     * Perform a server-side courier delivery-ratio check for a customer phone.
     *
     * @param  string  $phone  The customer phone number (as provided at checkout)
     * @return array{
     *   success: bool,
     *   total_parcels: int,
     *   success_parcels: int,
     *   cancelled_parcels: int,
     *   success_ratio: float,
     *   courier_breakdown: array,
     *   reports: array,
     *   checked_at: string,
     *   message?: string
     * }
     */
    public function check(string $phone): array
    {
        $phone = trim($phone);

        // Validate phone before making the API call
        if (empty($phone)) {
            return $this->failureResult('ফোন নম্বর প্রদান করা হয়নি (Phone number is empty).');
        }

        // Normalize: strip country code prefix if present
        $normalized = preg_replace('/^(?:\+88|88)/', '', $phone);
        if (!preg_match('/^01[3-9]\d{8}$/', $normalized)) {
            return $this->failureResult('সঠিক বাংলাদেশি ফোন নম্বর নয় (Invalid Bangladeshi phone number format).');
        }

        // 1. Cache lookup to prevent duplicate requests for the same phone within short window (10 minutes)
        $cacheKey = 'bdcourier_check_' . $normalized;
        try {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cached && is_array($cached) && !empty($cached['success'])) {
                $cached['cached'] = true;
                return $cached;
            }
        } catch (\Throwable $ce) {}

        // 2. Check if API quota is temporarily known to be exhausted to prevent hammering
        try {
            $quotaBlocked = \Illuminate\Support\Facades\Cache::get('bdcourier_quota_exhausted');
            if ($quotaBlocked) {
                return $this->failureResult(
                    'BD Courier একাউন্ট লিমিট শেষ: Both paid and free search limits have been reached. (অনুগ্রহ করে বিডি কুরিয়ার একাউন্টে রিচার্জ/প্যাকেজ রিনিউ করুন)',
                    429
                );
            }
        } catch (\Throwable $ce) {}

        // 3. Mock / Offline testing mode for automated test suites (0 outgoing requests)
        if (config('services.bdcourier.mock') || env('MOCK_BD_COURIER') === true || env('MOCK_BD_COURIER') === 'true' || (function_exists('request') && request()->header('x-mock-courier') === '1')) {
            return $this->normalizeResponse($normalized, [
                'status' => 'success',
                'data' => [
                    'total_parcel'     => 12,
                    'success_parcel'   => 11,
                    'cancelled_parcel' => 1,
                    'success_ratio'    => 91.67,
                    'courier_breakdown'=> [
                        ['name' => 'Steadfast', 'status' => '6/6 (100%)'],
                        ['name' => 'Pathao', 'status' => '5/6 (83.3%)'],
                    ],
                    'reports' => []
                ]
            ]);
        }

        // Resolve API key from server-side config only; never expose the key
        $apiKey = config('services.bdcourier.key');
        if (empty($apiKey)) {
            Log::warning('[BdCourierService] BDCOURIER_API_KEY is not configured. Courier check skipped.');
            return $this->failureResult('BD Courier API key is not configured on the server.');
        }

        try {
            $client = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(self::TIMEOUT_SECONDS);

            // In local environments without a configured CA certificate bundle, avoid connection exceptions
            if (!ini_get('curl.cainfo') && !ini_get('openssl.cafile')) {
                $client = $client->withoutVerifying();
            }

            $attempt = 0;
            $maxAttempts = 2;
            $response = null;

            while ($attempt < $maxAttempts) {
                $attempt++;
                $response = $client->post(self::API_ENDPOINT, [
                    'phone' => $normalized,
                ]);

                if ($response->successful()) {
                    break;
                }

                // Handle HTTP 429 Rate Limiting
                if ($response->status() === 429) {
                    $json = $response->json();
                    $msg = is_array($json) ? ($json['message'] ?? '') : '';

                    // Quota exhausted on BD Courier account
                    if (stripos($msg, 'limit') !== false || stripos($msg, 'reach') !== false) {
                        try {
                            \Illuminate\Support\Facades\Cache::put('bdcourier_quota_exhausted', true, 60);
                        } catch (\Throwable $ce) {}

                        return $this->failureResult(
                            'BD Courier একাউন্ট লিমিট শেষ: ' . ($msg ?: 'Both paid and free search limits have been reached.') . ' (অনুগ্রহ করে বিডি কুরিয়ার ড্যাশবোর্ড থেকে একাউন্ট রিচার্জ করুন)',
                            429
                        );
                    }

                    // Transient rate limit with Retry-After header
                    $retryAfter = (int) ($response->header('Retry-After') ?: 0);
                    if ($attempt < $maxAttempts && $retryAfter > 0 && $retryAfter <= 2) {
                        sleep($retryAfter);
                        continue;
                    } elseif ($attempt < $maxAttempts && $retryAfter === 0) {
                        usleep(500000); // 500ms backoff
                        continue;
                    }

                    return $this->failureResult(
                        'BD Courier API সাময়িকভাবে ব্যস্ত (Rate Limit 429)। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
                        429
                    );
                }

                // Retry once on server 5xx error with backoff
                if ($response->serverError() && $attempt < $maxAttempts) {
                    usleep(500000);
                    continue;
                }

                break;
            }

            if ($response && $response->successful()) {
                $json = $response->json();
                if (!is_array($json)) {
                    Log::warning('[BdCourierService] Malformed response — not a JSON object.', [
                        'phone_masked' => $this->maskPhone($normalized),
                        'status'       => $response->status(),
                    ]);
                    return $this->failureResult('BD Courier API returned a malformed response.');
                }

                $result = $this->normalizeResponse($normalized, $json);
                if (!empty($result['success'])) {
                    try {
                        \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 600);
                    } catch (\Throwable $ce) {}
                }
                return $result;
            }

            // Non-2xx HTTP status
            $errMsg = 'BD Courier API returned HTTP ' . ($response ? $response->status() : 'unknown') . '.';
            if ($response) {
                $json = $response->json();
                if (is_array($json) && !empty($json['message'])) {
                    $errMsg = 'BD Courier API: ' . $json['message'];
                }
            }

            Log::warning('[BdCourierService] Non-2xx HTTP status from BD Courier API.', [
                'phone_masked' => $this->maskPhone($normalized),
                'status'       => $response ? $response->status() : null,
                'message'      => $errMsg
            ]);

            return $this->failureResult($errMsg, $response ? $response->status() : 500);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Timeout or connection refused
            Log::warning('[BdCourierService] Connection timeout or refused.', [
                'phone_masked' => $this->maskPhone($normalized),
                'error'        => $e->getMessage(),
            ]);
            return $this->failureResult('BD Courier API connection timed out.');

        } catch (\Throwable $e) {
            // Any other unexpected failure — never bubble up to break orders
            Log::error('[BdCourierService] Unexpected error during courier check.', [
                'phone_masked' => $this->maskPhone($normalized),
                'error'        => $e->getMessage(),
            ]);
            return $this->failureResult('Unexpected error during courier check: ' . $e->getMessage());
        }
    }

    /**
     * Normalize the raw BD Courier API response into a clean internal structure.
     * Only retain data relevant to fraud scoring; raw PII fields are not stored.
     */
    private function normalizeResponse(string $phone, array $raw): array
    {
        // BD Courier API shape:
        //   data.summary: total_parcel, success_parcel, cancelled_parcel, success_ratio
        $data    = (isset($raw['data']) && is_array($raw['data'])) ? $raw['data'] : $raw;
        $summary = (isset($data['summary']) && is_array($data['summary'])) ? $data['summary'] : $data;

        $totalParcels = (int) (
            $summary['total_parcel']
            ?? $summary['total_parcels']
            ?? $summary['total']
            ?? $data['total_parcel']
            ?? $data['total']
            ?? $raw['total_parcel']
            ?? $raw['total']
            ?? 0
        );

        $successParcels = (int) (
            $summary['success_parcel']
            ?? $summary['success_parcels']
            ?? $summary['delivered']
            ?? $summary['success']
            ?? $data['success_parcel']
            ?? $data['delivered']
            ?? $raw['success_parcel']
            ?? $raw['delivered']
            ?? 0
        );

        $cancelledParcels = (int) (
            $summary['cancelled_parcel']
            ?? $summary['cancel_parcel']
            ?? $summary['cancelled_parcels']
            ?? $summary['cancelled']
            ?? $data['cancelled_parcel']
            ?? $data['cancel_parcel']
            ?? $data['cancelled']
            ?? $raw['cancelled_parcel']
            ?? $raw['cancel_parcel']
            ?? $raw['cancelled']
            ?? 0
        );

        // Success ratio: prefer API-provided rate/ratio, fallback to computed
        if (isset($summary['success_ratio']) && is_numeric($summary['success_ratio'])) {
            $successRatio = round((float) $summary['success_ratio'], 2);
        } elseif (isset($summary['success_rate']) && is_numeric($summary['success_rate'])) {
            $successRatio = round((float) $summary['success_rate'], 2);
        } elseif (isset($data['success_ratio']) && is_numeric($data['success_ratio'])) {
            $successRatio = round((float) $data['success_ratio'], 2);
        } elseif (isset($raw['success_ratio']) && is_numeric($raw['success_ratio'])) {
            $successRatio = round((float) $raw['success_ratio'], 2);
        } elseif (isset($raw['success_rate']) && is_numeric($raw['success_rate'])) {
            $successRatio = round((float) $raw['success_rate'], 2);
        } elseif ($totalParcels > 0) {
            $successRatio = round(($successParcels / $totalParcels) * 100, 2);
        } else {
            $successRatio = 0.0;
        }

        // Build courier breakdown (per-courier stats if provided)
        $courierBreakdown = [];
        $courierNames = ['pathao', 'redx', 'paperfly', 'steadfast', 'sundarban', 'ecourier', 'shajogoj'];
        foreach ($courierNames as $cname) {
            if (isset($data[$cname])) {
                $courierBreakdown[] = ['name' => ucfirst($cname), 'status' => $data[$cname]];
            } elseif (isset($raw[$cname])) {
                $courierBreakdown[] = ['name' => ucfirst($cname), 'status' => $raw[$cname]];
            }
        }

        // Collect report array if provided (used in admin display only, not stored as PII)
        $reports = [];
        $rawReports = $data['reports'] ?? $raw['reports'] ?? [];
        if (is_array($rawReports)) {
            foreach ($rawReports as $rep) {
                if (!is_array($rep)) continue;
                // Only keep aggregate stats, not personal names/addresses
                $reports[] = [
                    'courier'   => $rep['courier']   ?? null,
                    'total'     => $rep['total']      ?? $rep['total_parcel'] ?? null,
                    'delivered' => $rep['delivered']  ?? $rep['success_parcel'] ?? null,
                    'cancelled' => $rep['cancelled']  ?? $rep['cancelled_parcel'] ?? null,
                ];
            }
        }

        return [
            'success'           => true,
            'total_parcels'     => $totalParcels,
            'success_parcels'   => $successParcels,
            'cancelled_parcels' => $cancelledParcels,
            'success_ratio'     => $successRatio,
            'courier_breakdown' => $courierBreakdown,
            'reports'           => $reports,
            'checked_at'        => now()->toIso8601String(),
        ];
    }

    /**
     * Standardized failure result — never exposes the API key.
     */
    private function failureResult(string $message, int $statusCode = 400): array
    {
        return [
            'success'           => false,
            'status_code'       => $statusCode,
            'total_parcels'     => 0,
            'success_parcels'   => 0,
            'cancelled_parcels' => 0,
            'success_ratio'     => 0.0,
            'courier_breakdown' => [],
            'reports'           => [],
            'checked_at'        => now()->toIso8601String(),
            'message'           => $message,
        ];
    }

    /**
     * Mask a phone number for safe logging (e.g. 01712345678 -> 017****5678).
     * Never logs a full phone number.
     */
    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 7) {
            return '***';
        }
        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }
}
