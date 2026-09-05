<?php

namespace App\Services;

use App\Models\MetaTrackingEvent;
use InvalidArgumentException;

class MetaEventIdService
{
    /**
     * Maximum allowed character length for Meta event_id.
     */
    public const MAX_EVENT_ID_LENGTH = 64;

    /**
     * Generate a deterministic, order-specific Purchase event ID.
     * Guaranteed to produce the exact same ID for the same order number across Browser and Server.
     * Contains zero PII (no phone, email, address, or customer name).
     *
     * @param string $orderNumber Unique order/invoice identifier (e.g. "CB-20260905-ABC123" or "BFB-1002")
     * @return string e.g. "purchase_CB-20260905-ABC123"
     * @throws InvalidArgumentException
     */
    public function generatePurchaseEventId(string $orderNumber): string
    {
        $clean = trim($orderNumber);
        if (empty($clean)) {
            throw new InvalidArgumentException('Cannot generate Purchase event ID: Order number cannot be empty.');
        }

        // PII Safety Guard: Ensure no email or phone was accidentally passed as order number
        if (str_contains($clean, '@') || preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $clean)) {
            throw new InvalidArgumentException('PII detected: Order number must not be an email or raw phone number.');
        }

        // Sanitize to alphanumeric, dashes, and underscores
        $safeOrderNumber = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $clean);
        $eventId = "purchase_{$safeOrderNumber}";

        // Truncate safely if it exceeds 64 chars while keeping uniqueness
        if (strlen($eventId) > self::MAX_EVENT_ID_LENGTH) {
            $eventId = substr($eventId, 0, 56) . '_' . substr(hash('crc32b', $clean), 0, 7);
        }

        return $eventId;
    }

    /**
     * Generate a valid event ID for a given event name.
     *
     * @param string $eventName PageView, AddToCart, InitiateCheckout, Purchase
     * @param array $context Optional context (e.g. ['order_number' => 'CB-123'])
     * @return string
     * @throws InvalidArgumentException
     */
    public function generateForEvent(string $eventName, array $context = []): string
    {
        $normalized = strtolower(str_replace([' ', '_', '-'], '', trim($eventName)));

        return match ($normalized) {
            'purchase' => (!empty($context['order_number']) || !empty($context['order_id']) || !empty($context['invoice_no']))
                ? $this->generatePurchaseEventId(
                    $context['order_number'] ?? $context['order_id'] ?? $context['invoice_no']
                )
                : $this->generate('purchase'),
            'pageview' => $this->generate('pv'),
            'addtocart' => $this->generate('atc'),
            'initiatecheckout' => $this->generate('ic'),
            default => $this->generate('evt'),
        };
    }

    /**
     * Generate a unique, safe event ID string for non-deterministic events.
     * Always <= 64 characters, URL safe, containing zero secrets.
     */
    public function generate(string $prefix = 'evt'): string
    {
        $cleanPrefix = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(trim($prefix))) ?: 'evt';
        $timestamp = dechex((int) (microtime(true) * 1000));
        $random = bin2hex(random_bytes(6));

        $id = "{$cleanPrefix}_{$timestamp}_{$random}";
        return substr($id, 0, self::MAX_EVENT_ID_LENGTH);
    }

    /**
     * Check if an event ID is valid according to Meta Conversions API specifications:
     * - Length between 1 and 64 chars
     * - Alphanumeric, underscores, hyphens, and dots only
     * - Free of whitespace
     * - Free of email, phone, and credentials
     */
    public function isValid(?string $eventId): bool
    {
        if ($eventId === null || trim($eventId) === '') {
            return false;
        }

        $str = trim($eventId);

        if (strlen($str) > self::MAX_EVENT_ID_LENGTH) {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_\-\.]{1,64}$/', $str)) {
            return false;
        }

        // Must not contain email indicators
        if (str_contains($str, '@')) {
            return false;
        }

        // Must not contain sensitive tokens
        if (preg_match('/(?:EAAG|Bearer|token|secret|password)/i', $str)) {
            return false;
        }

        return true;
    }

    /**
     * Validate an event ID, throwing an exception if invalid.
     *
     * @throws InvalidArgumentException
     */
    public function validate(?string $eventId): bool
    {
        if (!$this->isValid($eventId)) {
            throw new InvalidArgumentException(
                "Invalid Meta event_id: '{$eventId}'. Must be 1-64 characters matching [A-Za-z0-9_.-] with no PII or secrets."
            );
        }

        return true;
    }

    /**
     * Check if a logical event has already been successfully sent for a given pixel.
     */
    public function isDuplicate(string $pixelId, string $eventName, string $eventId): bool
    {
        return MetaTrackingEvent::forPixelAndEvent($pixelId, $eventName, $eventId)
            ->where('server_status', MetaTrackingEvent::STATUS_SENT)
            ->exists();
    }
}
