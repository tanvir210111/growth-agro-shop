<?php

namespace App\Http\Middleware;

use App\Services\TrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip purely internal health-checks or static asset requests
        $path = $request->path();
        if ($this->shouldSkipTracking($path, $request)) {
            return $next($request);
        }

        // Initialize tracking context (resolves visitor, session, and queues cookies)
        $context = $this->trackingService->initializeFromRequest($request);

        // Record a single page_view event for regular GET requests
        if ($request->isMethod('GET') && !$request->ajax() && !$request->expectsJson()) {
            $this->trackingService->trackEvent(
                eventName: 'page_view',
                entityType: 'page',
                entityId: '/' . ltrim($path, '/'),
                properties: [
                    'page_type' => $context['session']?->page_type ?? 'custom_page',
                    'channel' => $context['session']?->channel ?? 'direct',
                ],
                request: $request
            );
        }

        return $next($request);
    }

    /**
     * Skip tracking for internal maintenance or asset patterns.
     */
    protected function shouldSkipTracking(string $path, Request $request): bool
    {
        $normalized = ltrim($path, '/');

        if ($normalized === 'up' || str_starts_with($normalized, '_debugbar')) {
            return true;
        }

        // Strictly exclude admin panel and internal API routes from marketing analytics
        if (str_starts_with($normalized, 'admin') || str_starts_with($normalized, 'api')) {
            return true;
        }

        // Skip static file extensions if routed through PHP
        $ext = pathinfo($normalized, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'woff', 'woff2', 'ttf', 'ico', 'map'])) {
            return true;
        }

        return false;
    }
}
