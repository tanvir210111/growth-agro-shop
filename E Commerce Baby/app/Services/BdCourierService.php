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
            return $this->failureResult('Phone number is empty — skipping courier check.');
        }

        // Normalize: strip country code prefix if present
        $normalized = preg_replace('/^(?:\+88|88)/', '', $phone);
        if (!preg_match('/^01[3-9]\d{8}$/', $normalized)) {
            return $this->failureResult('Invalid Bangladeshi phone number format — skipping courier check.');
        }

        // Resolve API key from server-side config only; never expose the key
        $apiKey = config('services.bdcourier.key');
        if (empty($apiKey)) {
            Log::warning('[BdCourierService] BDCOURIER_API_KEY is not configured. Courier check skipped.');
            return $this->failureResult('BD Courier API key is not configured on the server.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(self::TIMEOUT_SECONDS)
            ->post(self::API_ENDPOINT, [
                'phone' => $normalized,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (!is_array($json)) {
                    Log::warning('[BdCourierService] Malformed response — not a JSON object.', [
                        'phone_masked' => $this->maskPhone($normalized),
                        'status'       => $response->status(),
                    ]);
                    return $this->failureResult('BD Courier API returned a malformed response.');
                }

                return $this->normalizeResponse($normalized, $json);
            }

            // Non-2xx HTTP status
            Log::warning('[BdCourierService] Non-2xx HTTP status from BD Courier API.', [
                'phone_masked' => $this->maskPhone($normalized),
                'status'       => $response->status(),
            ]);
            return $this->failureResult('BD Courier API returned HTTP ' . $response->status() . '.');

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
            return $this->failureResult('Unexpected error during courier check.');
        }
    }

    /**
     * Normalize the raw BD Courier API response into a clean internal structure.
     * Only retain data relevant to fraud scoring; raw PII fields are not stored.
     */
    private function normalizeResponse(string $phone, array $raw): array
    {
        // BD Courier API shape (based on observed response structure):
        //   total, delivered, cancelled, success_rate, pathao, redx, paperfly, ...
        $totalParcels    = (int) ($raw['total']        ?? $raw['total_parcel']    ?? 0);
        $successParcels  = (int) ($raw['delivered']    ?? $raw['success_parcel']  ?? 0);
        $cancelledParcels = (int)($raw['cancelled']    ?? $raw['cancel_parcel']   ?? 0);

        // Success ratio: prefer API-provided rate, fallback to computed
        if (isset($raw['success_rate']) && is_numeric($raw['success_rate'])) {
            $successRatio = (float) $raw['success_rate'];
        } elseif ($totalParcels > 0) {
            $successRatio = round(($successParcels / $totalParcels) * 100, 2);
        } else {
            $successRatio = 0.0;
        }

        // Build courier breakdown (per-courier stats if provided)
        $courierBreakdown = [];
        $courierNames = ['pathao', 'redx', 'paperfly', 'steadfast', 'sundarban', 'ecourier', 'shajogoj'];
        foreach ($courierNames as $cname) {
            if (isset($raw[$cname])) {
                $courierBreakdown[] = ['name' => ucfirst($cname), 'status' => $raw[$cname]];
            }
        }

        // Collect report array if provided (used in admin display only, not stored as PII)
        $reports = [];
        if (isset($raw['reports']) && is_array($raw['reports'])) {
            foreach ($raw['reports'] as $rep) {
                // Only keep aggregate stats, not personal names/addresses
                $reports[] = [
                    'courier'   => $rep['courier']   ?? null,
                    'total'     => $rep['total']      ?? null,
                    'delivered' => $rep['delivered']  ?? null,
                    'cancelled' => $rep['cancelled']  ?? null,
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
    private function failureResult(string $message): array
    {
        return [
            'success'           => false,
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
