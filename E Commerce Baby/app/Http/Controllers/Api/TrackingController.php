<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;

    public const ALLOWED_EVENTS = [
        'page_view',
        'product_view',
        'category_view',
        'search',
        'cta_click',
        'add_to_cart',
        'checkout_started',
        'purchase',
    ];

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Ingest non-blocking client-side analytics event.
     * Always resolves visitor and session from authoritative server-side cookies.
     */
    public function recordEvent(Request $request): JsonResponse
    {
        try {
            // Support both standard JSON and text/plain (sent by navigator.sendBeacon)
            $payload = $request->json()->all();
            if (empty($payload)) {
                $raw = $request->getContent();
                if (!empty($raw)) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $payload = $decoded;
                    }
                }
            }

            $eventName = trim($payload['event_name'] ?? '');

            // Validate against strict allowlist
            if (!in_array($eventName, self::ALLOWED_EVENTS, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid event name.',
                ], 422);
            }

            $entityType = isset($payload['entity_type']) ? substr(trim((string)$payload['entity_type']), 0, 50) : null;
            $entityId = isset($payload['entity_id']) ? substr(trim((string)$payload['entity_id']), 0, 100) : null;
            $ctaIdentifier = isset($payload['cta_identifier']) ? substr(trim((string)$payload['cta_identifier']), 0, 100) : null;
            $pagePath = isset($payload['page_path']) ? substr(trim((string)$payload['page_path']), 0, 255) : null;
            $eventValue = isset($payload['event_value']) && is_numeric($payload['event_value']) ? (float)$payload['event_value'] : null;

            // Extract URL and Referrer context from frontend payload for landing pages
            $eventUrl = isset($payload['url']) ? trim((string)$payload['url']) : null;
            $eventReferrer = isset($payload['referrer']) ? trim((string)$payload['referrer']) : null;

            if (!empty($eventUrl)) {
                $request->attributes->set('event_url', $eventUrl);
                $parsed = parse_url($eventUrl);
                if (!empty($parsed['query'])) {
                    parse_str($parsed['query'], $queryParams);
                    foreach ($queryParams as $qk => $qv) {
                        if (!$request->query->has($qk) && is_scalar($qv)) {
                            $request->query->set($qk, (string)$qv);
                        }
                    }
                }
                if (!empty($parsed['path']) && empty($pagePath)) {
                    $pagePath = $parsed['path'];
                }
            }

            if (!empty($pagePath)) {
                $request->attributes->set('event_page_path', $pagePath);
            }

            if (!empty($eventReferrer) && !$request->headers->has('referer')) {
                $request->headers->set('referer', $eventReferrer);
                $request->attributes->set('event_referrer', $eventReferrer);
            }

            // Sanitize properties to prevent sensitive customer data leaks
            $rawProps = isset($payload['properties']) && is_array($payload['properties']) ? $payload['properties'] : [];
            $properties = $this->sanitizeProperties($rawProps);

            // Record event using authoritative server-side TrackingService
            $event = $this->trackingService->trackEvent(
                eventName: $eventName,
                entityType: $entityType,
                entityId: $entityId,
                properties: $properties,
                eventValue: $eventValue,
                ctaIdentifier: $ctaIdentifier,
                request: $request
            );

            return response()->json([
                'success' => true,
                'event_id' => $event?->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[TrackingController] Failed to record event: ' . $e->getMessage());
            // Keep fail-safe so client never experiences a fatal error
            return response()->json([
                'success' => false,
                'message' => 'Tracking unavailable.',
            ], 200);
        }
    }

    /**
     * Strip any sensitive PII or credentials if inadvertently submitted
     */
    protected function sanitizeProperties(array $props): array
    {
        $sensitiveKeys = [
            'password', 'token', 'auth', 'card', 'cvv', 'secret', 'phone', 
            'email', 'address', 'customer_phone', 'customer_address'
        ];

        $clean = [];
        foreach ($props as $k => $v) {
            $lowerKey = strtolower((string)$k);
            $isSensitive = false;
            foreach ($sensitiveKeys as $bad) {
                if (str_contains($lowerKey, $bad)) {
                    $isSensitive = true;
                    break;
                }
            }

            if (!$isSensitive) {
                if (is_scalar($v) || is_array($v)) {
                    $clean[$k] = $v;
                }
            }
        }

        return $clean;
    }
}
