<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Fraud check API (auth:api recommended for production)
Route::middleware('auth:api')->post('/fraud-check', function (Request $request) {
    $request->validate([
        'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
    ]);

    try {
        $report = \FraudCheckerBdCourier::check($request->phone);
        return response()->json(['status' => 'ok', 'data' => $report]);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
    }
});
