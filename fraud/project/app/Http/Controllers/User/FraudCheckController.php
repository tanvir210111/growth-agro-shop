<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FraudCheckLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Azmolla\FraudCheckerBdCourier\Facade\FraudCheckerBdCourier;

class FraudCheckController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recent = FraudCheckLog::where('user_id', $user->id)->orderBy('id', 'desc')->limit(10)->get();

        return view('user.fraud.index', compact('user', 'recent'));
    }

    public function check(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], [
            'phone.required' => 'মোবাইল নাম্বার দিতে হবে।',
            'phone.regex'    => 'সঠিক বাংলাদেশি নাম্বার দিন (যেমন: 017XXXXXXXX)।',
        ]);

        $user = Auth::user();
        $limitError = $this->assertUserCanCheck($user);
        if ($limitError) {
            return redirect()->back()->withInput()->with('error', $limitError);
        }

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
            'user_id'            => $user->id,
            'phone'              => $phone,
            'result'             => $report,
            'aggregate_success'  => (int) ($aggregate['total_success'] ?? 0),
            'aggregate_cancel'   => (int) ($aggregate['total_cancel'] ?? 0),
            'aggregate_total'    => (int) ($aggregate['total_deliveries'] ?? 0),
            'success_ratio'      => (float) ($aggregate['success_ratio'] ?? 0),
            'cancel_ratio'       => (float) ($aggregate['cancel_ratio'] ?? 0),
            'checked_by'         => 'user',
        ]);

        // Daily counter update
        if ($user->last_check_date !== now()->toDateString()) {
            $user->today_check_count = 0;
            $user->last_check_date = now()->toDateString();
        }
        $user->today_check_count = (int) $user->today_check_count + 1;
        $user->save();

        return view('user.fraud.result', [
            'phone'  => $phone,
            'report' => $report,
            'user'   => $user->fresh(),
        ]);
    }

    public function logs()
    {
        $logs = FraudCheckLog::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('user.fraud.logs', compact('logs'));
    }

    protected function assertUserCanCheck($user): ?string
    {
        if (!empty($user->fraud_check_expiry) && now()->gt($user->fraud_check_expiry)) {
            return 'আপনার Fraud Check প্যাকেজের মেয়াদ শেষ হয়েছে। রিনিউ করুন।';
        }

        $daily = (int) ($user->fraud_check_daily_limit ?? 0);
        if ($daily > 0) {
            $todayCount = (int) $user->today_check_count;
            if ($user->last_check_date !== now()->toDateString()) {
                $todayCount = 0;
            }
            if ($todayCount >= $daily) {
                return 'আজকের ডেইলি লিমিট শেষ (' . $daily . ')। আগামীকাল আবার চেষ্টা করুন।';
            }
        }

        $totalLimit = (int) ($user->fraud_check_limit ?? 0);
        if ($totalLimit > 0) {
            $used = FraudCheckLog::where('user_id', $user->id)->count();
            if ($used >= $totalLimit) {
                return 'আপনার টোটাল চেক লিমিট শেষ (' . $totalLimit . ')। প্যাকেজ আপগ্রেড করুন।';
            }
        }

        return null;
    }
}
