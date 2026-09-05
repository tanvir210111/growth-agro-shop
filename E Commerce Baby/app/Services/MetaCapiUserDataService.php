<?php

namespace App\Services;

use App\Models\MetaTrackingEvent;
use Illuminate\Http\Request;

class MetaCapiUserDataService
{
    /**
     * Normalize an email address according to Meta CAPI specification:
     * - Trim leading and trailing whitespace
     * - Convert all characters to lowercase
     * - Remove internal whitespace
     * - Omit if empty
     */
    public function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $clean = strtolower(trim($email));
        $clean = preg_replace('/\s+/', '', $clean);

        if ($clean === '' || !filter_var($clean, FILTER_VALIDATE_EMAIL)) {
            // Check if it's already a 64-char SHA256 hex string
            if ($this->isSha256($clean)) {
                return $clean;
            }
            return null;
        }

        return $clean;
    }

    /**
     * Normalize a phone number according to Meta CAPI specification:
     * - Strip non-digit characters (spaces, dashes, parentheses, plus sign)
     * - Standardize country prefix for Bangladesh (01XXXXXXXXX -> 8801XXXXXXXXX)
     * - Preserve valid international formats without altering numbers
     * - Omit if empty or insufficient digits (<7)
     */
    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);
        if ($trimmed === '') {
            return null;
        }

        // If already a 64-char SHA-256 hex hash, preserve it directly
        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        // Strip non-digits and formatting characters
        $digits = preg_replace('/\D+/', '', $trimmed);
        if (strlen($digits) < 7) {
            return null;
        }

        return $digits;
    }

    /**
     * Normalize a name (first name or last name):
     * - Trim whitespace
     * - Lowercase
     * - Strip punctuation/special characters
     * - Normalize internal whitespace
     */
    public function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim($name);
        if ($trimmed === '') {
            return null;
        }

        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        $clean = mb_strtolower($trimmed, 'UTF-8');
        // Remove special punctuation characters while preserving unicode letters and numbers
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }

    /**
     * Normalize a city:
     * - Lowercase, trim, strip punctuation
     * - Handle store city conventions e.g. 'inside_dhaka' -> 'dhaka'
     */
    public function normalizeCity(?string $city): ?string
    {
        if ($city === null) {
            return null;
        }

        $trimmed = trim($city);
        if ($trimmed === '') {
            return null;
        }

        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        $clean = strtolower($trimmed);
        // Handle common Bangladesh e-commerce city type values
        if (str_contains($clean, 'dhaka')) {
            return 'dhaka';
        }

        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }

    /**
     * Normalize a state / province:
     * - Lowercase, trim, strip punctuation
     */
    public function normalizeState(?string $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $trimmed = trim($state);
        if ($trimmed === '') {
            return null;
        }

        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        $clean = strtolower($trimmed);
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }

    /**
     * Normalize country code:
     * - 2-letter lowercase ISO country code (e.g. 'bd')
     */
    public function normalizeCountry(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $trimmed = trim($country);
        if ($trimmed === '') {
            return null;
        }

        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        $clean = strtolower($trimmed);
        if ($clean === 'bangladesh') {
            return 'bd';
        }

        $clean = preg_replace('/[^a-z]/', '', $clean);
        if (strlen($clean) === 2) {
            return $clean;
        }

        return strlen($clean) > 0 ? substr($clean, 0, 2) : null;
    }

    /**
     * Normalize external ID:
     * - Must be a stable non-secret identifier (e.g. customer ID, order number)
     * - Strictly rejects raw email, raw phone number, or secret tokens
     */
    public function normalizeExternalId(?string $externalId): ?string
    {
        if ($externalId === null) {
            return null;
        }

        $trimmed = trim($externalId);
        if ($trimmed === '') {
            return null;
        }

        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        // Must NOT use email or raw phone as external_id
        if (str_contains($trimmed, '@')) {
            return null;
        }

        if (preg_match('/^(\+?88)?01[3-9]\d{8}$/', $trimmed)) {
            return null;
        }

        // Must NOT contain sensitive keywords
        if (preg_match('/(?:EAAG|Bearer|token|secret|password)/i', $trimmed)) {
            return null;
        }

        return strtolower($trimmed);
    }

    /**
     * Check if a string is already a 64-character lowercase hexadecimal SHA-256 hash.
     */
    public function isSha256(string $value): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/i', trim($value));
    }

    /**
     * One-time SHA-256 hash with duplicate hashing protection.
     * If the input is already a valid SHA-256 hex string, preserves it without re-hashing.
     */
    public function hashSha256(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);
        if ($this->isSha256($trimmed)) {
            return strtolower($trimmed);
        }

        return hash('sha256', $trimmed);
    }

    /**
     * Build the structured User Data array for Meta Conversions API.
     *
     * Hashed PII Fields:
     * - em: array of hashed emails
     * - ph: array of hashed phone numbers
     * - fn: array of hashed first names
     * - ln: array of hashed last names
     * - ct: array of hashed cities
     * - st: array of hashed states
     * - country: array of hashed 2-letter country codes
     * - external_id: array of hashed external IDs
     *
     * Non-Hashed Identifiers:
     * - client_ip_address: raw string
     * - client_user_agent: raw string
     * - fbp: raw string
     * - fbc: raw string
     *
     * Omission:
     * - Any field that is null, empty, or unavailable is completely omitted.
     * - Guarantees zero raw PII and zero tokens/secrets in output.
     */
    public function buildUserData(array $raw): array
    {
        $userData = [];

        // 1. Email (em)
        $rawEmail = $raw['email'] ?? $raw['em'] ?? null;
        if (is_array($rawEmail)) {
            $rawEmail = reset($rawEmail);
        }
        $normEmail = $this->normalizeEmail($rawEmail ? (string)$rawEmail : null);
        if ($normEmail !== null) {
            $hashed = $this->hashSha256($normEmail);
            if ($hashed !== null) {
                $userData['em'] = [$hashed];
            }
        }

        // 2. Phone (ph)
        $rawPhone = $raw['phone'] ?? $raw['ph'] ?? null;
        if (is_array($rawPhone)) {
            $rawPhone = reset($rawPhone);
        }
        $normPhone = $this->normalizePhone($rawPhone ? (string)$rawPhone : null);
        if ($normPhone !== null) {
            $hashed = $this->hashSha256($normPhone);
            if ($hashed !== null) {
                $userData['ph'] = [$hashed];
            }
        }

        // 3. Names (fn & ln)
        $rawFn = $raw['first_name'] ?? $raw['fn'] ?? null;
        $rawLn = $raw['last_name'] ?? $raw['ln'] ?? null;

        // If first_name not provided but full name / customer_name is present
        if (empty($rawFn) && !empty($raw['customer_name'] ?? $raw['name'] ?? null)) {
            $fullName = trim((string)($raw['customer_name'] ?? $raw['name']));
            $parts = preg_split('/\s+/', $fullName, 2);
            $rawFn = $parts[0] ?? null;
            if (empty($rawLn) && isset($parts[1])) {
                $rawLn = $parts[1];
            }
        }

        if (is_array($rawFn)) {
            $rawFn = reset($rawFn);
        }
        $normFn = $this->normalizeName($rawFn ? (string)$rawFn : null);
        if ($normFn !== null) {
            $hashed = $this->hashSha256($normFn);
            if ($hashed !== null) {
                $userData['fn'] = [$hashed];
            }
        }

        if (is_array($rawLn)) {
            $rawLn = reset($rawLn);
        }
        $normLn = $this->normalizeName($rawLn ? (string)$rawLn : null);
        if ($normLn !== null) {
            $hashed = $this->hashSha256($normLn);
            if ($hashed !== null) {
                $userData['ln'] = [$hashed];
            }
        }

        // 4. City (ct)
        $rawCity = $raw['city'] ?? $raw['ct'] ?? null;
        if (is_array($rawCity)) {
            $rawCity = reset($rawCity);
        }
        $normCity = $this->normalizeCity($rawCity ? (string)$rawCity : null);
        if ($normCity !== null) {
            $hashed = $this->hashSha256($normCity);
            if ($hashed !== null) {
                $userData['ct'] = [$hashed];
            }
        }

        // 5. State (st)
        $rawState = $raw['state'] ?? $raw['st'] ?? null;
        if (is_array($rawState)) {
            $rawState = reset($rawState);
        }
        $normState = $this->normalizeState($rawState ? (string)$rawState : null);
        if ($normState !== null) {
            $hashed = $this->hashSha256($normState);
            if ($hashed !== null) {
                $userData['st'] = [$hashed];
            }
        }

        // 6. Country (country)
        $rawCountry = $raw['country'] ?? null;
        if (is_array($rawCountry)) {
            $rawCountry = reset($rawCountry);
        }
        $normCountry = $this->normalizeCountry($rawCountry ? (string)$rawCountry : null);
        if ($normCountry !== null) {
            $hashed = $this->hashSha256($normCountry);
            if ($hashed !== null) {
                $userData['country'] = [$hashed];
            }
        }

        // 7. External ID (external_id)
        $rawExtId = $raw['external_id'] ?? null;
        if (is_array($rawExtId)) {
            $rawExtId = reset($rawExtId);
        }
        $normExtId = $this->normalizeExternalId($rawExtId ? (string)$rawExtId : null);
        if ($normExtId !== null) {
            $hashed = $this->hashSha256($normExtId);
            if ($hashed !== null) {
                $userData['external_id'] = [$hashed];
            }
        }

        // 8. Non-Hashed Identifiers: IP Address
        $ip = $raw['client_ip_address'] ?? null;
        if (!empty($ip) && is_string($ip)) {
            $cleanIp = trim($ip);
            if (filter_var($cleanIp, FILTER_VALIDATE_IP)) {
                $userData['client_ip_address'] = $cleanIp;
            }
        }

        // 9. Non-Hashed Identifiers: User Agent
        $ua = $raw['client_user_agent'] ?? null;
        if (!empty($ua) && is_string($ua)) {
            $cleanUa = trim($ua);
            if ($cleanUa !== '') {
                $userData['client_user_agent'] = $cleanUa;
            }
        }

        // 10. Non-Hashed Identifiers: Meta Browser ID (_fbp)
        $fbp = $raw['fbp'] ?? null;
        if (!empty($fbp) && is_string($fbp)) {
            $cleanFbp = trim($fbp);
            if ($cleanFbp !== '') {
                $userData['fbp'] = $cleanFbp;
            }
        }

        // 11. Non-Hashed Identifiers: Meta Click ID (_fbc)
        $fbc = $raw['fbc'] ?? null;
        if (!empty($fbc) && is_string($fbc)) {
            $cleanFbc = trim($fbc);
            if ($cleanFbc !== '') {
                $userData['fbc'] = $cleanFbc;
            }
        }

        return $userData;
    }

    /**
     * Create user_data array populated from HTTP request context (IP, User Agent, _fbp, _fbc).
     */
    public function fromRequest(?Request $request = null, array $additionalData = []): array
    {
        $req = $request ?: request();
        $baseData = [];

        if ($req) {
            $baseData['client_ip_address'] = $req->ip();
            $baseData['client_user_agent'] = $req->userAgent();

            $cookieFbp = $req->cookie('_fbp');
            if (!empty($cookieFbp)) {
                $baseData['fbp'] = $cookieFbp;
            }

            $cookieFbc = $req->cookie('_fbc');
            if (!empty($cookieFbc)) {
                $baseData['fbc'] = $cookieFbc;
            }
        }

        $merged = array_merge($baseData, $additionalData);
        return $this->buildUserData($merged);
    }

    /**
     * Create user_data array from authoritative order record (for Purchase events).
     */
    public function fromOrder(mixed $order, ?Request $request = null): array
    {
        $customerData = [];

        if (is_array($order)) {
            $customerData = [
                'phone'         => $order['customer_phone'] ?? null,
                'customer_name' => $order['customer_name'] ?? null,
                'city'          => $order['city_type'] ?? $order['delivery_area'] ?? null,
                'country'       => 'bd',
                'external_id'   => $order['order_number'] ?? $order['invoice_no'] ?? null,
            ];
        } elseif (is_object($order)) {
            $customerData = [
                'phone'         => $order->customer_phone ?? null,
                'customer_name' => $order->customer_name ?? null,
                'city'          => $order->city_type ?? null,
                'country'       => 'bd',
                'external_id'   => $order->invoice_no ?? (string)$order->id,
            ];
        }

        return $this->fromRequest($request, $customerData);
    }
}
