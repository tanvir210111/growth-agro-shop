<?php

namespace App\Services;

use App\Models\MetaTrackingEvent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionApiService
{
    public const GRAPH_API_VERSION = 'v20.0';
    public const GRAPH_API_BASE_URL = 'https://graph.facebook.com';

    public const SUPPORTED_EVENTS = [
        'PageView',
        'AddToCart',
        'InitiateCheckout',
        'Purchase',
    ];

    protected MetaTrackingConfigService $configService;
    protected MetaEventIdService $eventIdService;
    protected MetaCapiUserDataService $userDataService;
    protected string $apiVersion;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(
        MetaTrackingConfigService $configService,
        ?MetaEventIdService $eventIdService = null,
        ?MetaCapiUserDataService $userDataService = null,
        string $apiVersion = self::GRAPH_API_VERSION,
        string $baseUrl = self::GRAPH_API_BASE_URL,
        int $timeout = 10
    ) {
        $this->configService = $configService;
        $this->eventIdService = $eventIdService ?? app(MetaEventIdService::class);
        $this->userDataService = $userDataService ?? app(MetaCapiUserDataService::class);
        $this->apiVersion = $apiVersion;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Send a single Meta CAPI event with strict event_id preservation and duplicate protection.
     *
     * @param array $eventData Structure:
     *   - event_name: string (Required: PageView, AddToCart, InitiateCheckout, Purchase)
     *   - event_id: string|null (Optional deterministic event identifier)
     *   - event_time: int|null (Optional epoch timestamp, defaults to now)
     *   - event_source_url: string|null (Optional URL where event took place)
     *   - action_source: string|null (Defaults to 'website')
     *   - user_data: array|null (Customer data for advanced matching)
     *   - custom_data: array|null (Event specifics: value, currency, contents, etc.)
     *   - order_id: string|null (Optional invoice/order number for tracking link)
     * @return array Standardized result array (zero tokens, safe for logging/inspection)
     */
    public function sendEvent(array $eventData): array
    {
        $rawEventName = trim((string) ($eventData['event_name'] ?? ''));
        $rawEventId = isset($eventData['event_id']) && trim((string) $eventData['event_id']) !== ''
            ? trim((string) $eventData['event_id'])
            : null;

        // 1. Central Event Name Validation
        $canonicalEventName = $this->normalizeEventName($rawEventName);
        if (!$canonicalEventName) {
            return [
                'success'         => false,
                'http_status'     => null,
                'event_name'      => $rawEventName ?: 'Unknown',
                'event_id'        => $rawEventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => "Unsupported event name: '{$rawEventName}'. Allowed: " . implode(', ', self::SUPPORTED_EVENTS),
            ];
        }

        // 2. Global Tracking Enabled Check
        if (!$this->configService->isTrackingEnabled()) {
            return [
                'success'         => false,
                'skipped'         => true,
                'http_status'     => null,
                'event_name'      => $canonicalEventName,
                'event_id'        => $rawEventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => 'Meta tracking is globally disabled in settings.',
            ];
        }

        // 3. Server Event Setting Toggle Check
        if (!$this->configService->isServerEventToggleEnabled($canonicalEventName)) {
            return [
                'success'         => false,
                'skipped'         => true,
                'http_status'     => null,
                'event_name'      => $canonicalEventName,
                'event_id'        => $rawEventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => "Server CAPI event '{$canonicalEventName}' is disabled in settings.",
            ];
        }

        // 4. Resolve Authoritative Active Pixel Configuration
        $targetPixelId = $eventData['pixel_id'] ?? null;
        $pixel = null;

        if (!empty($targetPixelId)) {
            $pixel = \App\Models\MetaPixel::where('pixel_id', $targetPixelId)->first();
        }

        if (!$pixel) {
            $pixel = $this->configService->getActivePixel();
        }

        if (!$pixel || empty($pixel->pixel_id)) {
            return [
                'success'         => false,
                'http_status'     => null,
                'event_name'      => $canonicalEventName,
                'event_id'        => $rawEventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => 'No active Meta Pixel ID configured in database.',
            ];
        }

        $pixelId = $pixel->pixel_id;

        // 5. Resolve Decrypted CAPI Token
        $accessToken = $pixel->getDecryptedAccessToken();
        if (empty($accessToken)) {
            return [
                'success'         => false,
                'http_status'     => null,
                'event_name'      => $canonicalEventName,
                'event_id'        => $rawEventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => 'No Meta CAPI access token configured for pixel.',
            ];
        }

        // 6. Resolve and Validate event_id (Must preserve supplied event_id without alteration)
        $eventId = $rawEventId;
        if ($eventId !== null) {
            if (!$this->eventIdService->isValid($eventId)) {
                return [
                    'success'         => false,
                    'http_status'     => null,
                    'event_name'      => $canonicalEventName,
                    'event_id'        => $eventId,
                    'meta_event_id'   => null,
                    'events_received' => 0,
                    'error_message'   => "Invalid event_id format: '{$eventId}'. Must be 1-64 characters matching [A-Za-z0-9_.-] with no PII.",
                ];
            }
        } else {
            // Generate appropriate ID if not supplied
            try {
                $eventId = $this->eventIdService->generateForEvent($canonicalEventName, [
                    'order_number' => $eventData['order_id'] ?? null,
                    'order_id'     => $eventData['order_id'] ?? null,
                ]);
            } catch (\InvalidArgumentException $iae) {
                return [
                    'success'         => false,
                    'http_status'     => null,
                    'event_name'      => $canonicalEventName,
                    'event_id'        => null,
                    'meta_event_id'   => null,
                    'events_received' => 0,
                    'error_message'   => 'Cannot generate event ID: ' . $iae->getMessage(),
                ];
            }
        }

        // 7. Server Idempotency & Duplicate Purchase Protection (Pixel + Event Name + Event ID)
        $existingRecord = MetaTrackingEvent::forPixelAndEvent($pixelId, $canonicalEventName, $eventId)->first();
        if ($existingRecord && $existingRecord->isServerSent()) {
            return [
                'success'              => true,
                'is_duplicate'         => true,
                'skipped'              => true,
                'http_status'          => $existingRecord->response_code ?: 200,
                'event_name'           => $canonicalEventName,
                'event_id'             => $eventId,
                'meta_event_id'        => $this->extractFbtraceId($existingRecord->response_body),
                'events_received'      => 0,
                'error_message'        => "Duplicate event: '{$canonicalEventName}' with ID '{$eventId}' has already been sent to Meta for this pixel.",
                'deduplication_status' => MetaTrackingEvent::DEDUP_DUPLICATE,
            ];
        }

        // 8. Build CAPI Payload with the exact event_id
        $eventData['event_id'] = $eventId;
        $testEventCode = $this->configService->getTestEventCode();
        $payload = $this->buildPayload($canonicalEventName, $eventData, $testEventCode);

        // 9. Execute Sanitized HTTP Request
        $endpoint = "{$this->baseUrl}/{$this->apiVersion}/{$pixelId}/events";
        $responseResult = $this->executeHttpRequest($endpoint, $accessToken, $payload, $canonicalEventName, $eventId);

        // 10. Update or Record Tracking Event in Database (Retry updates existing record with same event_id)
        $this->recordTrackingEvent($eventData, $canonicalEventName, $eventId, $pixelId, $responseResult, $existingRecord);

        return $responseResult;
    }

    /**
     * Build the structured payload conforming to Meta Conversions API specifications.
     */
    public function buildPayload(string $canonicalEventName, array $eventData, ?string $testEventCode): array
    {
        $eventTime = isset($eventData['event_time']) && is_numeric($eventData['event_time'])
            ? (int) $eventData['event_time']
            : time();

        $actionSource = !empty($eventData['action_source'])
            ? trim((string) $eventData['action_source'])
            : 'website';

        $eventSourceUrl = !empty($eventData['event_source_url'])
            ? trim((string) $eventData['event_source_url'])
            : null;

        $eventId = !empty($eventData['event_id'])
            ? trim((string) $eventData['event_id'])
            : null;

        $userData = $this->buildUserData($eventData['user_data'] ?? []);
        $customData = $this->buildCustomData($eventData['custom_data'] ?? []);

        $singleEvent = [
            'event_name'    => $canonicalEventName,
            'event_time'    => $eventTime,
            'action_source' => $actionSource,
        ];

        if ($eventId !== null && $eventId !== '') {
            $singleEvent['event_id'] = $eventId;
        }

        if ($eventSourceUrl !== null && $eventSourceUrl !== '') {
            $singleEvent['event_source_url'] = $eventSourceUrl;
        }

        if (!empty($userData)) {
            $singleEvent['user_data'] = $userData;
        }

        if (!empty($customData)) {
            $singleEvent['custom_data'] = $customData;
        }

        $payload = [
            'data' => [$singleEvent],
        ];

        // Include Test Event Code only if non-empty
        if (!empty($testEventCode) && trim($testEventCode) !== '') {
            $payload['test_event_code'] = trim($testEventCode);
        }

        return $payload;
    }

    /**
     * Build and hash user data according to Meta CAPI specification.
     * Delegates to centralized MetaCapiUserDataService.
     */
    public function buildUserData(array $raw): array
    {
        return $this->userDataService->buildUserData($raw);
    }

    /**
     * Get the injected MetaCapiUserDataService instance.
     */
    public function getUserDataService(): MetaCapiUserDataService
    {
        return $this->userDataService;
    }

    /**
     * Build Custom Data payload without fabricating values.
     */
    public function buildCustomData(array $raw): array
    {
        $customData = [];

        if (isset($raw['value']) && is_numeric($raw['value'])) {
            $customData['value'] = round((float) $raw['value'], 2);
        }

        if (!empty($raw['currency'])) {
            $customData['currency'] = strtoupper(trim((string) $raw['currency']));
        }

        if (isset($raw['content_ids']) && is_array($raw['content_ids'])) {
            $customData['content_ids'] = array_values(array_map('strval', $raw['content_ids']));
        }

        if (isset($raw['contents']) && is_array($raw['contents'])) {
            $customData['contents'] = array_values($raw['contents']);
        }

        if (!empty($raw['content_type'])) {
            $customData['content_type'] = trim((string) $raw['content_type']);
        }

        if (isset($raw['num_items']) && is_numeric($raw['num_items'])) {
            $customData['num_items'] = (int) $raw['num_items'];
        }

        if (!empty($raw['order_id'])) {
            $customData['order_id'] = trim((string) $raw['order_id']);
        }

        return $customData;
    }

    /**
     * Canonical event name resolver.
     */
    public function normalizeEventName(string $name): ?string
    {
        $clean = strtolower(str_replace([' ', '_', '-'], '', trim($name)));
        return match ($clean) {
            'pageview'         => 'PageView',
            'addtocart'        => 'AddToCart',
            'initiatecheckout' => 'InitiateCheckout',
            'purchase'         => 'Purchase',
            default            => null,
        };
    }

    /**
     * Execute HTTP request to Meta Graph API with comprehensive error handling and secret scrubbing.
     */
    protected function executeHttpRequest(string $endpoint, string $accessToken, array $payload, string $eventName, ?string $eventId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post($endpoint, $payload);

            $status = $response->status();
            $rawBody = $response->body();
            $decoded = $response->json() ?: [];

            // Scrub any secrets from the response before inspection or logging
            $sanitizedBody = MetaTrackingEvent::scrubSecrets($rawBody);

            if ($response->successful()) {
                $eventsReceived = (int) ($decoded['events_received'] ?? 1);
                $fbtraceId = $decoded['fbtrace_id'] ?? null;

                return [
                    'success'         => true,
                    'http_status'     => $status,
                    'event_name'      => $eventName,
                    'event_id'        => $eventId,
                    'meta_event_id'   => $fbtraceId,
                    'events_received' => $eventsReceived,
                    'error_message'   => null,
                    'response_body'   => $sanitizedBody,
                ];
            }

            // HTTP 4xx or 5xx Error handling
            $errorInfo = $decoded['error'] ?? [];
            $errorMessage = !empty($errorInfo['message'])
                ? MetaTrackingEvent::scrubSecrets((string) $errorInfo['message'])
                : "Meta API returned error HTTP {$status}";

            return [
                'success'         => false,
                'http_status'     => $status,
                'event_name'      => $eventName,
                'event_id'        => $eventId,
                'meta_event_id'   => $decoded['fbtrace_id'] ?? ($errorInfo['fbtrace_id'] ?? null),
                'events_received' => 0,
                'error_message'   => $errorMessage,
                'response_body'   => $sanitizedBody,
            ];
        } catch (ConnectionException $ce) {
            $sanitizedMsg = MetaTrackingEvent::scrubSecrets($ce->getMessage());
            Log::warning("[Meta CAPI Timeout/Connection Error] {$sanitizedMsg}");

            return [
                'success'         => false,
                'http_status'     => null,
                'event_name'      => $eventName,
                'event_id'        => $eventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => 'Connection to Meta Conversions API timed out: ' . $sanitizedMsg,
                'response_body'   => null,
            ];
        } catch (\Throwable $e) {
            $sanitizedMsg = MetaTrackingEvent::scrubSecrets($e->getMessage());
            Log::error("[Meta CAPI Unexpected Error] {$sanitizedMsg}");

            return [
                'success'         => false,
                'http_status'     => null,
                'event_name'      => $eventName,
                'event_id'        => $eventId,
                'meta_event_id'   => null,
                'events_received' => 0,
                'error_message'   => 'Failed to dispatch Meta CAPI event: ' . $sanitizedMsg,
                'response_body'   => null,
            ];
        }
    }

    /**
     * Safely record or update tracking event status in meta_tracking_events table.
     * Uses updateOrCreate to ensure retry updates the existing record using the exact same event_id.
     */
    protected function recordTrackingEvent(
        array $eventData,
        string $eventName,
        string $eventId,
        string $pixelId,
        array $result,
        ?MetaTrackingEvent $existingRecord = null
    ): void {
        try {
            $orderId = !empty($eventData['order_id']) ? (string) $eventData['order_id'] : ($existingRecord?->order_id ?? null);
            $actionSource = $eventData['action_source'] ?? ($existingRecord?->action_source ?? 'website');
            $eventSourceUrl = $eventData['event_source_url'] ?? ($existingRecord?->event_source_url ?? null);

            MetaTrackingEvent::updateOrCreate(
                [
                    'pixel_id'   => $pixelId,
                    'event_name' => $eventName,
                    'event_id'   => $eventId,
                ],
                [
                    'order_id'             => $orderId,
                    'order_source'         => $eventData['order_source'] ?? ($existingRecord?->order_source ?? ($eventName === 'Purchase' ? 'MAIN_WEBSITE' : null)),
                    'action_source'        => $actionSource,
                    'event_source_url'     => $eventSourceUrl,
                    'user_data'            => $this->buildUserData($eventData['user_data'] ?? ($existingRecord?->user_data ?? [])),
                    'custom_data'          => $this->buildCustomData($eventData['custom_data'] ?? ($existingRecord?->custom_data ?? [])),
                    'browser_status'       => $existingRecord?->browser_status ?? MetaTrackingEvent::STATUS_PENDING,
                    'server_status'        => $result['success'] ? MetaTrackingEvent::STATUS_SENT : (!empty($result['skipped']) ? MetaTrackingEvent::STATUS_SKIPPED : MetaTrackingEvent::STATUS_FAILED),
                    'deduplication_status' => !empty($result['is_duplicate']) ? MetaTrackingEvent::DEDUP_DUPLICATE : ($existingRecord?->deduplication_status ?? MetaTrackingEvent::DEDUP_PENDING),
                    'purchase_mode'        => $eventData['purchase_mode'] ?? ($existingRecord?->purchase_mode ?? $this->configService->getPurchaseEventMode()),
                    'scheduled_at'         => $existingRecord?->scheduled_at ?? now(),
                    'sent_at'              => $result['success'] ? now() : ($existingRecord?->sent_at ?? null),
                    'attempt_count'        => ($existingRecord?->attempt_count ?? 0) + 1,
                    'last_attempt_at'      => now(),
                    'response_code'        => $result['http_status'],
                    'response_body'        => $result['response_body'] ?? ($existingRecord?->response_body ?? null),
                    'error_message'        => $result['error_message'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            // Fail open: DB event logging should never interrupt service flow
            Log::warning('[MetaConversionApiService] DB logging fail-open: ' . MetaTrackingEvent::scrubSecrets($e->getMessage()));
        }
    }

    /**
     * Extract fbtrace_id from sanitized response body if available.
     */
    protected function extractFbtraceId(?string $responseBody): ?string
    {
        if (empty($responseBody)) {
            return null;
        }

        try {
            $data = json_decode($responseBody, true);
            return $data['fbtrace_id'] ?? ($data['error']['fbtrace_id'] ?? null);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * SHA-256 helper for PII hashing.
     */
    protected function hashSha256(string $value): string
    {
        // Avoid double hashing if string is already a 64-char hex string
        if (preg_match('/^[a-f0-9]{64}$/i', $value)) {
            return strtolower($value);
        }

        return hash('sha256', $value);
    }
}
