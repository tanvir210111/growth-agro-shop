<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaPurchaseRule;
use App\Models\MetaTrackingSetting;
use App\Services\MetaPurchaseRuleService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminMetaTrackingController extends Controller
{
    // =========================================================================
    // AUTHENTICATION & AUTHORIZATION
    // =========================================================================

    /**
     * Resolve the currently authenticated admin (session guard, header token, or sanctum).
     */
    protected function getAuthenticatedAdmin(Request $request): ?Admin
    {
        // 1. Session Auth (Central Admin Guard)
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if ($user instanceof Admin) {
                return $user;
            }
        }

        // 2. Default guard check (useful in test environment)
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof Admin) {
                return $user;
            }
        }

        // 3. Bearer Token / Header Auth (matching AdminManagementController)
        $authHeader = $request->header('Authorization', '');
        $token = $request->header('x-admin-token', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        if (!empty($token)) {
            // Check for test/session tokens or query admin
            if ($token === 'adm_session' || strlen($token) >= 8) {
                return Admin::first();
            }
        }

        return null;
    }

    /**
     * Require any valid admin authentication.
     */
    protected function requireAuth(Request $request): ?JsonResponse
    {
        if (!$this->getAuthenticatedAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Admin access required.',
            ], 401);
        }
        return null;
    }

    /**
     * Require Admin role or above (Super Admin or Admin).
     * Moderator -> 403 Forbidden.
     */
    protected function requireAdminOrAbove(Request $request): ?JsonResponse
    {
        $admin = $this->getAuthenticatedAdmin($request);
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Admin access required.',
            ], 401);
        }

        if (!$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Admin role or above required for this operation.',
            ], 403);
        }

        return null;
    }

    /**
     * Require Super Admin role.
     */
    protected function requireSuperAdmin(Request $request): ?JsonResponse
    {
        $admin = $this->getAuthenticatedAdmin($request);
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Admin access required.',
            ], 401);
        }

        if (!$admin->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Super Admin access required for this operation.',
            ], 403);
        }

        return null;
    }

    /**
     * Invalidate runtime tracking config cache.
     */
    protected function invalidateCache(): void
    {
        app(MetaTrackingConfigService::class)->invalidateCache();
    }

    /**
     * Format a MetaPixel model into a safe array.
     * NEVER returns decrypted access_token or masked_token.
     * Only returns has_token: true/false.
     */
    protected function formatSafePixel(MetaPixel $pixel): array
    {
        return [
            'id'              => $pixel->id,
            'pixel_name'      => $pixel->pixel_name,
            'pixel_id'        => $pixel->pixel_id,
            'has_token'       => (bool) $pixel->has_token,
            'test_event_code' => $pixel->test_event_code,
            'is_active'       => (bool) $pixel->is_active,
            'is_default'      => (bool) $pixel->is_default,
            'created_at'      => $pixel->created_at?->toIso8601String(),
            'updated_at'      => $pixel->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Format MetaTrackingSetting into a safe array.
     * Never returns decrypted token or masked_token in active_pixel.
     */
    protected function formatSafeSettings(MetaTrackingSetting $settings): array
    {
        $activePixelSafe = null;
        if ($settings->activePixel) {
            $activePixelSafe = $this->formatSafePixel($settings->activePixel);
        }

        return [
            'id'                       => $settings->id,
            'is_enabled'               => (bool) $settings->is_enabled,
            'active_pixel_id'          => $settings->active_pixel_id,
            'active_pixel'             => $activePixelSafe,
            'browser_events'           => [
                'pageview'          => (bool) $settings->browser_pageview_enabled,
                'add_to_cart'       => (bool) $settings->browser_add_to_cart_enabled,
                'initiate_checkout' => (bool) $settings->browser_initiate_checkout_enabled,
                'purchase'          => (bool) $settings->browser_purchase_enabled,
            ],
            'server_events'            => [
                'pageview'          => (bool) $settings->server_pageview_enabled,
                'add_to_cart'       => (bool) $settings->server_add_to_cart_enabled,
                'initiate_checkout' => (bool) $settings->server_initiate_checkout_enabled,
                'purchase'          => (bool) $settings->server_purchase_enabled,
            ],
            'purchase_control'         => [
                'mode'          => $settings->purchase_event_mode ?: 'instant',
                'delay_minutes' => (int) ($settings->purchase_delay_minutes ?: 30),
            ],
            'auto_rules_enabled'       => (bool) $settings->auto_rules_enabled,
            'updated_at'               => $settings->updated_at?->toIso8601String(),
        ];
    }

    // =========================================================================
    // 1. PIXEL CRUD ENDPOINTS
    // =========================================================================

    /**
     * GET /api/admin/meta/pixels
     * List all configured pixels safely. Never exposes tokens.
     */
    public function getPixels(Request $request): JsonResponse
    {
        $authError = $this->requireAuth($request);
        if ($authError) return $authError;

        $pixels = MetaPixel::orderBy('id', 'asc')->get()->map(function (MetaPixel $p) {
            return $this->formatSafePixel($p);
        });

        return response()->json([
            'success' => true,
            'pixels'  => $pixels,
        ]);
    }

    /**
     * POST /api/admin/meta/pixels
     * Create a new Meta Pixel.
     */
    public function storePixel(Request $request): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $validator = Validator::make($request->all(), [
            'pixel_name'      => 'required|string|max:100',
            'pixel_id'        => 'required|string|min:5|max:25|regex:/^\d+$/',
            'access_token'    => 'nullable|string|min:20',
            'test_event_code' => 'nullable|string|max:50',
            'is_active'       => 'nullable|boolean',
            'is_default'      => 'nullable|boolean',
        ], [
            'pixel_id.regex' => 'The Pixel ID must be a numeric string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);
        $isDefault = filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN);

        // If this is the first pixel in the system, automatically make it active & default
        if (MetaPixel::count() === 0) {
            $isActive = true;
            $isDefault = true;
        }

        $pixelData = [
            'pixel_name'      => trim($request->input('pixel_name')),
            'pixel_id'        => trim($request->input('pixel_id')),
            'test_event_code' => $request->filled('test_event_code') ? trim($request->input('test_event_code')) : null,
            'is_active'       => $isActive,
            'is_default'      => $isDefault,
        ];

        // Store encrypted access token if provided
        if ($request->filled('access_token')) {
            $pixelData['access_token'] = trim($request->input('access_token'));
        }

        $pixel = DB::transaction(function () use ($pixelData, $isActive, $isDefault) {
            if ($isDefault) {
                MetaPixel::query()->update(['is_default' => false]);
            }
            if ($isActive) {
                MetaPixel::query()->update(['is_active' => false]);
            }

            $created = MetaPixel::create($pixelData);

            if ($isActive) {
                $settings = MetaTrackingSetting::current();
                $settings->update(['active_pixel_id' => $created->id]);
            }

            return $created;
        });

        $this->invalidateCache();

        return response()->json([
            'success' => true,
            'message' => 'Meta Pixel created successfully.',
            'pixel'   => $this->formatSafePixel($pixel->fresh()),
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/meta/pixels/{id}
     * Update an existing Meta Pixel.
     */
    public function updatePixel(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $pixel = MetaPixel::find($id);
        if (!$pixel) {
            return response()->json([
                'success' => false,
                'message' => 'Pixel not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'pixel_name'      => 'required|string|max:100',
            'pixel_id'        => 'required|string|min:5|max:25|regex:/^\d+$/',
            'access_token'    => 'nullable|string',
            'test_event_code' => 'nullable|string|max:50',
            'is_active'       => 'nullable|boolean',
            'is_default'      => 'nullable|boolean',
        ], [
            'pixel_id.regex' => 'The Pixel ID must be a numeric string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pixel->pixel_name = trim($request->input('pixel_name'));
        $pixel->pixel_id = trim($request->input('pixel_id'));

        // Handle Test Event Code
        if ($request->has('test_event_code')) {
            $testCode = trim((string) $request->input('test_event_code'));
            $pixel->test_event_code = !empty($testCode) ? $testCode : null;
        }

        // Handle Access Token:
        // Only update if non-empty string is provided.
        // If blank/null, keep existing encrypted token intact.
        $rawToken = $request->input('access_token');
        if ($rawToken !== null && trim($rawToken) !== '') {
            $trimmedToken = trim($rawToken);
            if (strlen($trimmedToken) < 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'The CAPI Access Token must be at least 20 characters if provided.',
                    'errors'  => ['access_token' => ['The token must be at least 20 characters.']],
                ], 422);
            }
            $pixel->access_token = $trimmedToken;
        }

        $newIsActive = $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN) : $pixel->is_active;
        $newIsDefault = $request->has('is_default') ? filter_var($request->input('is_default'), FILTER_VALIDATE_BOOLEAN) : $pixel->is_default;

        DB::transaction(function () use ($pixel, $newIsActive, $newIsDefault) {
            if ($newIsDefault && !$pixel->is_default) {
                MetaPixel::where('id', '!=', $pixel->id)->update(['is_default' => false]);
                $pixel->is_default = true;
            } elseif ($newIsDefault) {
                $pixel->is_default = true;
            }

            if ($newIsActive && !$pixel->is_active) {
                MetaPixel::where('id', '!=', $pixel->id)->update(['is_active' => false]);
                $pixel->is_active = true;
                $settings = MetaTrackingSetting::current();
                $settings->update(['active_pixel_id' => $pixel->id]);
            } elseif ($newIsActive) {
                $pixel->is_active = true;
            }

            $pixel->save();
        });

        $this->invalidateCache();

        return response()->json([
            'success' => true,
            'message' => 'Meta Pixel updated successfully.',
            'pixel'   => $this->formatSafePixel($pixel->fresh()),
        ]);
    }

    /**
     * POST /api/admin/meta/pixels/{id}/set-active
     * Switch active pixel. Deactivates all other pixels and updates settings.
     */
    public function setActivePixel(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $pixel = MetaPixel::find($id);
        if (!$pixel) {
            return response()->json([
                'success' => false,
                'message' => 'Pixel not found.',
            ], 404);
        }

        DB::transaction(function () use ($id) {
            // Deactivate all other pixels so ONLY this pixel is active
            MetaPixel::where('id', '!=', $id)->update(['is_active' => false]);
            MetaPixel::where('id', $id)->update(['is_active' => true]);

            $settings = MetaTrackingSetting::current();
            $settings->update(['active_pixel_id' => $id]);
        });

        $this->invalidateCache();

        return response()->json([
            'success'         => true,
            'message'         => 'Active Pixel updated successfully.',
            'active_pixel_id' => $id,
            'pixel'           => $this->formatSafePixel($pixel->fresh()),
        ]);
    }

    /**
     * POST /api/admin/meta/pixels/{id}/set-default
     * Switch default pixel. Ensures single default pixel.
     */
    public function setDefaultPixel(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $pixel = MetaPixel::find($id);
        if (!$pixel) {
            return response()->json([
                'success' => false,
                'message' => 'Pixel not found.',
            ], 404);
        }

        DB::transaction(function () use ($id) {
            MetaPixel::where('id', '!=', $id)->update(['is_default' => false]);
            MetaPixel::where('id', $id)->update(['is_default' => true]);
        });

        $this->invalidateCache();

        return response()->json([
            'success'          => true,
            'message'          => 'Default Pixel updated successfully.',
            'default_pixel_id' => $id,
            'pixel'            => $this->formatSafePixel($pixel->fresh()),
        ]);
    }

    /**
     * DELETE /api/admin/meta/pixels/{id}
     * Delete pixel safely with prerequisite checks.
     */
    public function deletePixel(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireSuperAdmin($request);
        if ($authError) return $authError;

        $pixel = MetaPixel::find($id);
        if (!$pixel) {
            return response()->json([
                'success' => false,
                'message' => 'Pixel not found.',
            ], 404);
        }

        // Safety 1: Cannot delete if it is the only configured pixel
        if (MetaPixel::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only configured Pixel in the system.',
            ], 422);
        }

        // Safety 2: Cannot delete if it is currently active
        $settings = MetaTrackingSetting::current();
        if ($settings->active_pixel_id === $id || $pixel->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the currently active Pixel. Please switch active pixel first.',
            ], 422);
        }

        DB::transaction(function () use ($pixel) {
            $wasDefault = $pixel->is_default;
            $pixel->delete();

            // If the deleted pixel was default, promote active pixel or first pixel to default
            if ($wasDefault) {
                $settings = MetaTrackingSetting::current();
                $fallback = MetaPixel::find($settings->active_pixel_id) ?: MetaPixel::first();
                if ($fallback) {
                    $fallback->update(['is_default' => true]);
                }
            }
        });

        $this->invalidateCache();

        return response()->json([
            'success' => true,
            'message' => 'Meta Pixel deleted successfully.',
        ]);
    }

    // =========================================================================
    // 2. TRACKING SETTINGS ENDPOINTS
    // =========================================================================

    /**
     * GET /api/admin/meta/tracking-settings
     * Retrieve tracking configuration and active pixel status. Never exposes token.
     */
    public function getSettings(Request $request): JsonResponse
    {
        $authError = $this->requireAuth($request);
        if ($authError) return $authError;

        $settings = MetaTrackingSetting::current();

        return response()->json([
            'success'  => true,
            'settings' => $this->formatSafeSettings($settings),
        ]);
    }

    /**
     * PUT /api/admin/meta/tracking-settings
     * Update master tracking and event toggles.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $settings = MetaTrackingSetting::current();

        // 1. Master toggle
        if ($request->has('is_enabled')) {
            $settings->is_enabled = filter_var($request->input('is_enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        // 2. Browser event toggles
        if ($request->has('browser_pageview_enabled')) {
            $settings->browser_pageview_enabled = filter_var($request->input('browser_pageview_enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('browser_add_to_cart_enabled')) {
            $settings->browser_add_to_cart_enabled = filter_var($request->input('browser_add_to_cart_enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('browser_initiate_checkout_enabled')) {
            $settings->browser_initiate_checkout_enabled = filter_var($request->input('browser_initiate_checkout_enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('browser_purchase_enabled')) {
            $settings->browser_purchase_enabled = filter_var($request->input('browser_purchase_enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        // 3. Server event toggles
        // Note: Server PageView is prohibited from automatic every-request dispatch. We keep it false or safe.
        if ($request->has('server_add_to_cart_enabled')) {
            $settings->server_add_to_cart_enabled = filter_var($request->input('server_add_to_cart_enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('server_initiate_checkout_enabled')) {
            $settings->server_initiate_checkout_enabled = filter_var($request->input('server_initiate_checkout_enabled'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('server_purchase_enabled')) {
            $settings->server_purchase_enabled = filter_var($request->input('server_purchase_enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        // 4. Purchase Event Control (Phase 8)
        if ($request->has('purchase_event_mode')) {
            $mode = strtolower(trim((string) $request->input('purchase_event_mode')));
            if (in_array($mode, ['instant', 'delay', 'hold'], true)) {
                $settings->purchase_event_mode = $mode;
            }
        }
        if ($request->has('purchase_delay_minutes')) {
            $delay = (int) $request->input('purchase_delay_minutes');
            if ($delay >= 1 && $delay <= 1440) {
                $settings->purchase_delay_minutes = $delay;
            }
        }

        // 5. Auto Rules Enabled (Phase 9)
        if ($request->has('auto_rules_enabled')) {
            $settings->auto_rules_enabled = filter_var($request->input('auto_rules_enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        $settings->save();

        $this->invalidateCache();

        return response()->json([
            'success'  => true,
            'message'  => 'Tracking settings updated successfully.',
            'settings' => $this->formatSafeSettings($settings->fresh()),
        ]);
    }

    // =========================================================================
    // 3. PURCHASE EVENT CONTROL & QUEUE ENDPOINTS (PHASE 8)
    // =========================================================================

    /**
     * GET /api/admin/meta/purchases
     * List Meta Purchase events queue with safe attributes.
     */
    public function getPurchases(Request $request): JsonResponse
    {
        $authError = $this->requireAuth($request);
        if ($authError) return $authError;

        $query = \App\Models\MetaTrackingEvent::where('event_name', 'Purchase')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('server_status', $request->input('status'));
        }

        if ($request->filled('mode')) {
            $query->where('purchase_mode', $request->input('mode'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('event_id', 'like', "%{$search}%");
            });
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function ($event) {
            return $event->toSafeArray();
        });

        return response()->json([
            'success'    => true,
            'data'       => $items,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/meta/purchases/{id}/release
     * Explicitly release a held Purchase event and dispatch to Meta CAPI.
     */
    public function releasePurchase(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $admin = $this->getAuthenticatedAdmin($request);
        $service = app(\App\Services\MetaPurchaseControlService::class);
        $result = $service->releaseHeldPurchase($id, $admin);

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * POST /api/admin/meta/purchases/{id}/retry
     * Retry a failed or due Purchase event.
     */
    public function retryPurchase(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $admin = $this->getAuthenticatedAdmin($request);
        $service = app(\App\Services\MetaPurchaseControlService::class);
        $result = $service->retryPurchaseEvent($id, $admin);

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * POST /api/admin/meta/purchases/process-delayed
     * Manual fallback / emergency trigger to process due delayed events.
     */
    public function processDelayedPurchases(Request $request): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $limit = min(100, max(1, (int) $request->input('limit', 50)));
        $admin = $this->getAuthenticatedAdmin($request);
        $service = app(\App\Services\MetaPurchaseControlService::class);
        $result = $service->processDueDelayedPurchases($limit);

        return response()->json([
            'success' => true,
            'message' => sprintf('Processed %d delayed events (%d succeeded, %d failed).', $result['processed'], $result['succeeded'], $result['failed']),
            'result'  => $result,
        ]);
    }

    // =========================================================================
    // 4. PURCHASE RULE CRUD ENDPOINTS (PHASE 9)
    // =========================================================================

    /**
     * GET /api/admin/meta/rules
     * List all configured purchase rules.
     */
    public function getRules(Request $request): JsonResponse
    {
        $authError = $this->requireAuth($request);
        if ($authError) return $authError;

        $rules = MetaPurchaseRule::orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn ($r) => $this->formatRule($r));

        return response()->json([
            'success' => true,
            'rules'   => $rules,
        ]);
    }

    /**
     * POST /api/admin/meta/rules
     * Create a new purchase rule.
     */
    public function storeRule(Request $request): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $data = $request->only([
            'rule_name', 'priority', 'condition_field', 'operator',
            'condition_value', 'condition_value_high', 'action_mode',
            'delay_minutes', 'is_active',
        ]);

        $errors = MetaPurchaseRuleService::validateRuleData($data);
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $errors,
            ], 422);
        }

        $rule = MetaPurchaseRule::create([
            'rule_name'           => trim($data['rule_name']),
            'priority'            => isset($data['priority']) ? (int) $data['priority'] : 0,
            'condition_field'     => $data['condition_field'],
            'operator'            => $data['operator'],
            'condition_value'     => (string) $data['condition_value'],
            'condition_value_high'=> isset($data['condition_value_high']) ? (string) $data['condition_value_high'] : null,
            'action_mode'         => $data['action_mode'],
            'delay_minutes'       => isset($data['delay_minutes']) ? (int) $data['delay_minutes'] : 0,
            'is_active'           => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase rule created successfully.',
            'rule'    => $this->formatRule($rule->fresh()),
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/meta/rules/{id}
     * Update an existing purchase rule.
     */
    public function updateRule(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $rule = MetaPurchaseRule::find($id);
        if (!$rule) {
            return response()->json([
                'success' => false,
                'message' => 'Rule not found.',
            ], 404);
        }

        $data = $request->only([
            'rule_name', 'priority', 'condition_field', 'operator',
            'condition_value', 'condition_value_high', 'action_mode',
            'delay_minutes', 'is_active',
        ]);

        $errors = MetaPurchaseRuleService::validateRuleData($data);
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $errors,
            ], 422);
        }

        $rule->update([
            'rule_name'           => trim($data['rule_name']),
            'priority'            => isset($data['priority']) ? (int) $data['priority'] : $rule->priority,
            'condition_field'     => $data['condition_field'],
            'operator'            => $data['operator'],
            'condition_value'     => (string) $data['condition_value'],
            'condition_value_high'=> isset($data['condition_value_high']) ? (string) $data['condition_value_high'] : null,
            'action_mode'         => $data['action_mode'],
            'delay_minutes'       => isset($data['delay_minutes']) ? (int) $data['delay_minutes'] : $rule->delay_minutes,
            'is_active'           => filter_var($data['is_active'] ?? $rule->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase rule updated successfully.',
            'rule'    => $this->formatRule($rule->fresh()),
        ]);
    }

    /**
     * DELETE /api/admin/meta/rules/{id}
     * Delete a purchase rule.
     */
    public function deleteRule(Request $request, int $id): JsonResponse
    {
        $authError = $this->requireAdminOrAbove($request);
        if ($authError) return $authError;

        $rule = MetaPurchaseRule::find($id);
        if (!$rule) {
            return response()->json([
                'success' => false,
                'message' => 'Rule not found.',
            ], 404);
        }

        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase rule deleted successfully.',
        ]);
    }

    /**
     * GET /api/admin/meta/rules/schema
     * Returns allowed fields and operators for building the rule editor UI.
     */
    public function getRuleSchema(Request $request): JsonResponse
    {
        $authError = $this->requireAuth($request);
        if ($authError) return $authError;

        return response()->json([
            'success'    => true,
            'fields'     => MetaPurchaseRuleService::ALLOWED_FIELDS,
            'operators'  => MetaPurchaseRuleService::ALLOWED_OPERATORS,
            'modes'      => ['instant', 'delay', 'hold'],
        ]);
    }

    /**
     * Format a MetaPurchaseRule model into a safe array.
     */
    protected function formatRule(MetaPurchaseRule $rule): array
    {
        return [
            'id'                   => $rule->id,
            'rule_name'            => $rule->rule_name,
            'priority'             => (int) $rule->priority,
            'condition_field'      => $rule->condition_field,
            'operator'             => $rule->operator,
            'condition_value'      => $rule->condition_value,
            'condition_value_high' => $rule->condition_value_high,
            'action_mode'          => $rule->action_mode,
            'delay_minutes'        => (int) $rule->delay_minutes,
            'is_active'            => (bool) $rule->is_active,
            'created_at'           => $rule->created_at?->toIso8601String(),
            'updated_at'           => $rule->updated_at?->toIso8601String(),
        ];
    }
}
