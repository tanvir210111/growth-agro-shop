<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    /**
     * Authenticate admin credentials against MySQL database.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Look up admin by email in admins table
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Establish real server session
        Auth::guard('admin')->login($admin, $request->boolean('remember', true));
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful.',
            'user'    => [
                'id'    => $admin->id,
                'name'  => $admin->name,
                'email' => $admin->email,
                'role'  => $admin->role ?? 'Admin',
            ],
        ]);
    }

    /**
     * Check if current session is authenticated.
     */
    public function me(Request $request): JsonResponse
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            return response()->json([
                'authenticated' => true,
                'user'          => [
                    'id'    => $admin->id,
                    'name'  => $admin->name,
                    'email' => $admin->email,
                    'role'  => $admin->role ?? 'Admin',
                ],
            ]);
        }

        return response()->json([
            'authenticated' => false,
            'message'       => 'Unauthenticated.',
        ], 401);
    }

    /**
     * Terminate the admin session.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
