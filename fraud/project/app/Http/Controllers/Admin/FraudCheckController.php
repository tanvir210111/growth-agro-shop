<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudCheckLog;
use Illuminate\Http\Request;
use Azmolla\FraudCheckerBdCourier\Facade\FraudCheckerBdCourier;

class FraudCheckController extends Controller
{
    public function index()
    {
        $recent = FraudCheckLog::orderBy('id', 'desc')->limit(10)->get();
        return view('admin.fraud.index', compact('recent'));
    }

    public function check(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], [
            'phone.required' => 'মোবাইল নাম্বার দিতে হবে।',
            'phone.regex'    => 'সঠিক বাংলাদেশি নাম্বার দিন (যেমন: 017XXXXXXXX)।',
        ]);

        $phone = $request->phone;

        try {
            $report = FraudCheckerBdCourier::check($phone);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'চেক ব্যর্থ: ' . $e->getMessage());
        }

        $aggregate = $report['aggregate'] ?? [];

        FraudCheckLog::create([
            'admin_id'           => auth('admin')->id(),
            'phone'              => $phone,
            'result'             => $report,
            'aggregate_success'  => (int) ($aggregate['total_success'] ?? 0),
            'aggregate_cancel'   => (int) ($aggregate['total_cancel'] ?? 0),
            'aggregate_total'    => (int) ($aggregate['total_deliveries'] ?? 0),
            'success_ratio'      => (float) ($aggregate['success_ratio'] ?? 0),
            'cancel_ratio'       => (float) ($aggregate['cancel_ratio'] ?? 0),
            'checked_by'         => 'admin',
        ]);

        return view('admin.fraud.result', [
            'phone'  => $phone,
            'report' => $report,
        ]);
    }

    public function logs(Request $request)
    {
        $query = FraudCheckLog::orderBy('id', 'desc');

        if ($request->filled('q')) {
            $query->where('phone', 'like', '%' . $request->q . '%');
        }

        $logs = $query->paginate(30)->appends($request->query());

        return view('admin.fraud.logs', compact('logs'));
    }

    public function destroy($id)
    {
        FraudCheckLog::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'লগ ডিলিট করা হয়েছে।');
    }
}
