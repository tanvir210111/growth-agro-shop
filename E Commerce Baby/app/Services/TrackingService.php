<?php

namespace App\Services;

use App\Models\Order;
use App\Models\TrackingEvent;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrackingService
{
    public const COOKIE_VISITOR = 'growth_agro_visitor_id';
    public const COOKIE_SESSION = 'growth_agro_session_id';

    public const VISITOR_LIFETIME_MINUTES = 525600; // 1 year (365 days)
    public const SESSION_INACTIVITY_MINUTES = 30;   // 30 minutes inactivity window

    protected ?TrackingVisitor $currentVisitor = null;
    protected ?TrackingSession $currentSession = null;

    /**
     * Initialize tracking context from an incoming HTTP Request.
     * Resolves or creates Visitor and Session, queues cookies, and stores context in request attributes.
     */
    public function initializeFromRequest(Request $request): array
    {
        try {
            $visitor = $this->resolveVisitor($request);
            $session = $this->resolveSession($request, $visitor);

            $this->currentVisitor = $visitor;
            $this->currentSession = $session;

            $request->attributes->set('tracking_visitor', $visitor);
            $request->attributes->set('tracking_session', $session);

            return [
                'visitor' => $visitor,
                'session' => $session,
            ];
        } catch (\Throwable $e) {
            Log::warning('[TrackingService] Failed to initialize tracking context: ' . $e->getMessage());
            return [
                'visitor' => null,
                'session' => null,
            ];
        }
    }

    /**
     * Resolve existing visitor or create a new unique visitor record.
     */
    public function resolveVisitor(Request $request): TrackingVisitor
    {
        if ($this->currentVisitor) {
            return $this->currentVisitor;
        }

        $cookieUuid = $request->cookie(self::COOKIE_VISITOR)
            ?: $request->input('visitor_uuid')
            ?: $request->header('x-visitor-id');

        if ($cookieUuid) {
            $visitor = TrackingVisitor::where('visitor_uuid', $cookieUuid)->first();
            if ($visitor) {
                // Update last seen timestamp
                $visitor->update(['last_seen_at' => now()]);
                $this->currentVisitor = $visitor;
                $this->queueVisitorCookie($request, $visitor->visitor_uuid);
                return $visitor;
            }
        }

        // Create new Visitor record
        $newUuid = (string) Str::uuid();
        $referrer = $request->attributes->get('event_referrer') ?: $request->headers->get('referer');
        $referrerDomain = $this->extractReferrerDomain($referrer);
        $channel = $this->classifyChannel($request, $referrerDomain);

        $landingPage = $request->attributes->get('event_page_path')
            ?: $request->input('landing_page')
            ?: ('/' . ltrim($request->path(), '/'));

        $visitor = TrackingVisitor::create([
            'visitor_uuid' => $newUuid,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'first_source' => $request->query('utm_source') ?: $channel,
            'first_utm_campaign' => $request->query('utm_campaign'),
            'first_landing_page' => substr($landingPage, 0, 255),
            'total_orders' => 0,
            'total_revenue' => 0,
        ]);

        $this->currentVisitor = $visitor;
        $this->queueVisitorCookie($request, $newUuid);

        return $visitor;
    }

    /**
     * Resolve active session (within 30-min window) or create a new session record.
     */
    public function resolveSession(Request $request, TrackingVisitor $visitor): TrackingSession
    {
        if ($this->currentSession) {
            return $this->currentSession;
        }

        $sessionUuid = $request->cookie(self::COOKIE_SESSION)
            ?: $request->input('session_uuid')
            ?: $request->header('x-session-id');

        if ($sessionUuid) {
            $session = TrackingSession::where('session_uuid', $sessionUuid)
                ->where('visitor_id', $visitor->id)
                ->first();

            if ($session) {
                $lastActivity = $session->updated_at ?: $session->session_start;
                if ($lastActivity && $lastActivity->diffInMinutes(now()) <= self::SESSION_INACTIVITY_MINUTES) {
                    // Session is still active: update duration and touch
                    $duration = max(0, now()->diffInSeconds($session->session_start));
                    $session->update([
                        'duration_seconds' => $duration,
                        'session_end' => now(),
                    ]);

                    $this->currentSession = $session;
                    $this->queueSessionCookie($request, $session->session_uuid);
                    return $session;
                }
            }
        }

        // Inactive or new session: create fresh record
        $newSessionUuid = (string) Str::uuid();
        $path = $request->attributes->get('event_page_path')
            ?: $request->input('landing_page')
            ?: ('/' . ltrim($request->path(), '/'));
        $referrer = $request->attributes->get('event_referrer') ?: $request->headers->get('referer');
        $referrerDomain = $this->extractReferrerDomain($referrer);
        $channel = $this->classifyChannel($request, $referrerDomain);
        $clientInfo = $this->detectClient($request->userAgent() ?? '');
        $entryUrl = $request->attributes->get('event_url') ?: substr($request->fullUrl(), 0, 500);

        $clickId = $request->query('fbclid') 
            ?? $request->query('gclid') 
            ?? $request->query('ttclid');

        $session = TrackingSession::create([
            'session_uuid' => $newSessionUuid,
            'visitor_id' => $visitor->id,
            'session_start' => now(),
            'session_end' => now(),
            'duration_seconds' => 0,
            'entry_url' => substr($entryUrl, 0, 500),
            'landing_page_path' => substr($path, 0, 255),
            'page_type' => $this->determinePageType($path),
            'referrer_url' => $referrer ? substr($referrer, 0, 500) : null,
            'referrer_domain' => $referrerDomain ? substr($referrerDomain, 0, 100) : null,
            'channel' => $channel,
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content' => $request->query('utm_content'),
            'utm_term' => $request->query('utm_term'),
            'click_id' => $clickId,
            'device_type' => $clientInfo['device_type'],
            'browser' => $clientInfo['browser'],
            'os' => $clientInfo['os'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_converted' => false,
        ]);

        $this->currentSession = $session;
        $this->queueSessionCookie($request, $newSessionUuid);

        return $session;
    }

    /**
     * Record a behavioral event stream item (page_view, product_view, add_to_cart, etc.)
     */
    public function trackEvent(
        string $eventName,
        ?string $entityType = null,
        ?string $entityId = null,
        array $properties = [],
        ?float $eventValue = null,
        ?string $ctaIdentifier = null,
        ?Request $request = null
    ): ?TrackingEvent {
        try {
            $req = $request ?? request();
            $visitor = $this->currentVisitor ?? ($req ? $this->resolveVisitor($req) : null);
            $session = $this->currentSession ?? ($req && $visitor ? $this->resolveSession($req, $visitor) : null);

            $pagePath = $req ? ('/' . ltrim($req->path(), '/')) : null;

            // 1. Purchase Event Deduplication (Strict idempotency by Order/Invoice Number)
            if ($eventName === 'purchase' && !empty($entityId)) {
                $existingPurchase = TrackingEvent::where('event_name', 'purchase')
                    ->where('entity_id', $entityId)
                    ->first();
                if ($existingPurchase) {
                    return $existingPurchase;
                }
            }

            // 2. Rapid Page View Deduplication (within 2 seconds for identical path in same session)
            if ($eventName === 'page_view' && $session && !empty($pagePath)) {
                $recentPageView = TrackingEvent::where('session_id', $session->id)
                    ->where('event_name', 'page_view')
                    ->where('page_path', substr($pagePath, 0, 255))
                    ->where('created_at', '>=', now()->subSeconds(2))
                    ->first();
                if ($recentPageView) {
                    return $recentPageView;
                }
            }

            return TrackingEvent::create([
                'session_id' => $session?->id,
                'visitor_id' => $visitor?->id,
                'event_name' => $eventName,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'cta_identifier' => $ctaIdentifier,
                'page_path' => $pagePath ? substr($pagePath, 0, 255) : null,
                'event_value' => $eventValue,
                'properties' => !empty($properties) ? $properties : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[TrackingService] Failed to record event (' . $eventName . '): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Attach tracking attribution context to an Order record.
     * Updates nullable attribution columns, marks session converted, and updates visitor lifetime metrics.
     */
    public function attachOrderAttribution(Order $order, ?Request $request = null): Order
    {
        try {
            $req = $request ?? request();
            $visitor = $this->currentVisitor ?? ($req ? $this->resolveVisitor($req) : null);
            $session = $this->currentSession ?? ($req && $visitor ? $this->resolveSession($req, $visitor) : null);

            if ($session) {
                $order->visitor_id = $visitor?->id;
                $order->session_id = $session->id;
                if (empty($order->source_type)) {
                    $isLandingPage = ($session->page_type === 'landing_page'
                        || str_starts_with($session->landing_page_path ?? '', '/products/')
                        || str_starts_with($order->landing_page ?? '', '/products/'));
                    $order->source_type = $isLandingPage ? 'LANDING' : 'MAIN_WEBSITE';
                }
                $order->landing_page = $order->landing_page ?: $session->landing_page_path;
                $order->utm_source = $session->utm_source;
                $order->utm_medium = $session->utm_medium;
                $order->utm_campaign = $session->utm_campaign;
                $order->utm_content = $session->utm_content;
                $order->referrer_domain = $session->referrer_domain;
                $order->click_id = $session->click_id;
                $order->device_type = $session->device_type;
                $order->ip_address = $session->ip_address ?: $req?->ip();
                $order->save();

                // Mark session converted
                $session->update([
                    'is_converted' => true,
                    'order_id' => $order->id,
                ]);

                // Update visitor lifetime metrics
                if ($visitor) {
                    $orderRevenue = (float) ($order->total_amount ?? $order->total ?? 0);
                    $visitor->increment('total_orders');
                    $visitor->increment('total_revenue', $orderRevenue);

                    if (!$visitor->customer_phone && !empty($order->customer_phone)) {
                        $visitor->update(['customer_phone' => $order->customer_phone]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[TrackingService] Failed to attach order attribution: ' . $e->getMessage());
        }

        return $order;
    }

    /**
     * Deterministic channel classification.
     */
    public function classifyChannel(Request $request, ?string $referrerDomain): string
    {
        $utmSource = strtolower(trim($request->query('utm_source', '')));
        $utmMedium = strtolower(trim($request->query('utm_medium', '')));
        $hasGclid = $request->has('gclid');
        $hasFbclid = $request->has('fbclid');
        $hasTtclid = $request->has('ttclid');

        // 1. Paid Search
        if ($hasGclid || ($utmSource === 'google' && in_array($utmMedium, ['cpc', 'ppc', 'paidsearch'])) || in_array($utmMedium, ['cpc', 'ppc'])) {
            if (in_array($utmSource, ['google', 'bing', 'yahoo', 'duckduckgo']) || $hasGclid) {
                return 'paid_search';
            }
        }

        // 2. Paid Social
        $socialSources = ['facebook', 'fb', 'instagram', 'meta', 'tiktok', 'youtube', 'snapchat', 'pinterest', 'twitter', 'x'];
        $paidMediums = ['cpc', 'cpm', 'paid', 'paidsocial', 'paid_social', 'ad', 'ads', 'story', 'reel', 'boost'];
        if ($hasFbclid || $hasTtclid || (in_array($utmSource, $socialSources) && in_array($utmMedium, $paidMediums))) {
            return 'paid_social';
        }

        // 3. Email
        if (in_array($utmMedium, ['email', 'newsletter', 'em'])) {
            return 'email';
        }

        // 4. Organic Social (UTM source is social without paid medium)
        if (in_array($utmSource, $socialSources)) {
            return 'organic_social';
        }

        // 5. Referrer Domain Analysis (No campaign UTMs)
        if ($referrerDomain) {
            $ref = strtolower($referrerDomain);

            // Organic Search
            $searchEngines = ['google.', 'bing.', 'yahoo.', 'duckduckgo.', 'ecosia.', 'baidu.'];
            foreach ($searchEngines as $se) {
                if (str_contains($ref, $se)) {
                    return 'organic_search';
                }
            }

            // Organic Social
            foreach ($socialSources as $soc) {
                if (str_contains($ref, $soc)) {
                    return 'organic_social';
                }
            }

            // External Referral (Exclude internal Growth Agro domains)
            $internalHosts = ['growthagro.shop', '127.0.0.1', 'localhost'];
            $isInternal = false;
            foreach ($internalHosts as $ih) {
                if (str_contains($ref, $ih)) {
                    $isInternal = true;
                    break;
                }
            }

            if (!$isInternal) {
                return 'referral';
            }
        }

        // 6. Direct / Unknown
        return 'direct';
    }

    /**
     * Classify page type based on path.
     */
    public function determinePageType(string $path): string
    {
        $normalized = '/' . trim($path, '/');

        if ($normalized === '/' || $normalized === '') {
            return 'storefront_home';
        }

        if (str_starts_with($normalized, '/products/')) {
            return 'landing_page';
        }

        if (str_starts_with($normalized, '/product/')) {
            return 'product_detail';
        }

        if (str_starts_with($normalized, '/shop')) {
            return 'shop_catalog';
        }

        if (str_starts_with($normalized, '/collections/')) {
            return 'category_collection';
        }

        if (str_starts_with($normalized, '/checkout')) {
            return 'checkout';
        }

        if (str_starts_with($normalized, '/cart')) {
            return 'cart';
        }

        if (str_starts_with($normalized, '/admin')) {
            return 'admin';
        }

        return 'custom_page';
    }

    /**
     * Extract clean host/domain from referrer URL.
     */
    public function extractReferrerDomain(?string $referrer): ?string
    {
        if (!$referrer) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        return $host ? strtolower($host) : null;
    }

    /**
     * Lightweight Client/Device/OS/Browser parser without heavy dependencies.
     */
    public function detectClient(string $userAgent): array
    {
        $ua = strtolower($userAgent);

        // 1. Device Type
        $deviceType = 'desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|iphone|ipod|android.*mobile|blackberry|bb10|opera mini|iemobile|wpdesktop/i', $ua)) {
            $deviceType = 'mobile';
        }

        // 2. Operating System
        $os = 'Other';
        if (str_contains($ua, 'android')) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (str_contains($ua, 'windows nt')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'mac os x')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'Linux';
        }

        // 3. Browser
        $browser = 'Other';
        if (str_contains($ua, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'samsungbrowser')) {
            $browser = 'Samsung Internet';
        } elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($ua, 'chrome') || str_contains($ua, 'crios')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'firefox') || str_contains($ua, 'fxios')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) {
            $browser = 'Safari';
        }

        return [
            'device_type' => $deviceType,
            'os' => $os,
            'browser' => $browser,
        ];
    }

    /**
     * Get currently resolved visitor instance.
     */
    public function getCurrentVisitor(): ?TrackingVisitor
    {
        return $this->currentVisitor;
    }

    /**
     * Get currently resolved session instance.
     */
    public function getCurrentSession(): ?TrackingSession
    {
        return $this->currentSession;
    }

    protected function queueVisitorCookie(Request $request, string $uuid): void
    {
        Cookie::queue(
            Cookie::make(
                self::COOKIE_VISITOR,
                $uuid,
                self::VISITOR_LIFETIME_MINUTES,
                '/',
                null,
                $request->isSecure(),
                false, // httpOnly false allows safe client-side reading for analytics events
                false,
                'Lax'
            )
        );
    }

    protected function queueSessionCookie(Request $request, string $uuid): void
    {
        Cookie::queue(
            Cookie::make(
                self::COOKIE_SESSION,
                $uuid,
                self::SESSION_INACTIVITY_MINUTES,
                '/',
                null,
                $request->isSecure(),
                false,
                false,
                'Lax'
            )
        );
    }
}
