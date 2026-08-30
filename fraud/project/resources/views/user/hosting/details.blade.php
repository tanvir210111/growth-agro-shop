@extends('layouts.front')
@section('title', 'Manage Hosting - ' . $hosting->domain)
@section('contents')

{{-- ফন্ট, আইকন এবং প্রিমিয়াম লাইব্রেরি --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #334155; }


/* স্ট্যাটাস ব্যাজ (ডায়নামিক কালার) */
    .status-badge { padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; }
    
    .status-active { background: #dcfce7; color: #15803d; }
    .status-suspended { background: #fee2e2; color: #991b1b; }
    .status-pending { background: #fef9c3; color: #854d0e; }


    /* ================= আপনার সাইডবার স্টাইল (অপরিবর্তিত) ================= */
    .sidebar-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #edf2f7; position: sticky; top: 20px; }
    .user-profile-box { text-align: center; padding: 35px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; }
    .user-avatar-wrapper { position: relative; width: 100px; height: 100px; margin: 0 auto 15px; }
    .user-avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #4e73df; background-color: #eee; }
    .menu-link { display: flex; align-items: center; padding: 14px 20px; color: #64748b; text-decoration: none; border-radius: 12px; margin: 5px 15px; transition: 0.3s; font-weight: 500; font-size: 15px; }
    .menu-link i { width: 30px; font-size: 18px; }
    .menu-link:hover, .menu-link.active { background: #f1f5f9; color: #4e73df; }
    .menu-link.active { font-weight: 700; background: #eef2ff; color: #4e73df; }

    /* ================= সুপার প্রিমিয়াম ডিটেইলস এরিয়া ================= */
    .main-content-wrapper { background: transparent; }
    .premium-card { background: #fff; border-radius: 24px; border: none; box-shadow: 0 4px 25px rgba(0,0,0,0.03); padding: 30px; margin-bottom: 25px; }
    
    /* হেডার সেকশন */
    .service-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
    .domain-title { font-size: 24px; font-weight: 800; color: #1e293b; margin: 0; letter-spacing: -0.5px; }
    
    /* স্ট্যাটাস ব্যাজ */
    .badge-active { background: #dcfce7; color: #15803d; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; }
    
    /* ইনফো গ্রিড */
    .info-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .info-value { font-size: 15px; font-weight: 700; color: #334155; }

    /* অ্যাকশন টাইলস (cPanel Style) */
    .action-tile { 
        background: #ffffff; border: 1px solid #f1f5f9; border-radius: 20px; padding: 25px 15px; 
        text-align: center; transition: 0.3s; text-decoration: none; display: block; height: 100%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }
    .action-tile:hover { transform: translateY(-8px); border-color: #4e73df; box-shadow: 0 15px 30px rgba(78, 115, 223, 0.12); }
    .tile-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; }
    .tile-title { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 5px; display: block; }
    .tile-desc { font-size: 11px; color: #94a3b8; line-height: 1.4; display: block; }

    /* কালার থিমস */
    .bg-soft-orange { background: #fff7ed; color: #f97316; }
    .bg-soft-blue { background: #eff6ff; color: #3b82f6; }
    .bg-soft-green { background: #f0fdf4; color: #22c55e; }
    .bg-soft-purple { background: #faf5ff; color: #a855f7; }

    /* ইউজড বার */
    .usage-container { margin-top: 20px; }
    .usage-header { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 12px; font-weight: 700; }
    .progress-thin { height: 6px; border-radius: 10px; background: #f1f5f9; }
</style>

<div class="section-padding py-5">
    <div class="container">
        <div class="row g-4">
            
            {{-- সাইডবার (বামে) --}}
            <div class="col-lg-3">
                <div class="sidebar-card">
                    <div class="user-profile-box">
                        <div class="user-avatar-wrapper">
                            <img src="{{ Auth::user()->photo ? asset('assets/images/users/'.Auth::user()->photo) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png' }}" class="user-avatar">
                        </div>
                        <h6 class="fw-bold text-dark mb-0">{{ Auth::user()->name }}</h6>
                        <span class="badge bg-light text-primary border mt-2">ক্লায়েন্ট</span>
                    </div>
                    <div class="py-3">
                        <a href="{{ route('user.dashboard') }}" class="menu-link"><i class="fa-solid fa-table-columns"></i> ড্যাশবোর্ড</a>
<a href="{{ route('user.domains.index') }}" class="menu-link {{ request()->routeIs('user.domains.index') ? 'active' : '' }}">
    <i class="fa-solid fa-globe"></i> আমার ডোমেইন
</a>
{{-- আমার হোস্টিং মেনু লিংক --}}
<a href="{{ route('user.hostings.index') }}" class="menu-link {{ request()->routeIs('user.hostings.index') ? 'active' : '' }}">
    <i class="fa-solid fa-server"></i> আমার হোস্টিং
</a>
<style>
    .submenu-container {
    background: #fdfdfd; /* সাব-মেনুর জন্য হালকা আলাদা ব্যাকগ্রাউন্ড */
    border-radius: 12px;
    margin: 2px 15px;
}

.ps-5 {
    padding-left: 45px !important; /* সাব-মেনু আইটেমগুলোকে একটু ভেতরে সরানোর জন্য */
    font-size: 14px !important;    /* সাব-মেনু ফন্ট একটু ছোট */
}

.menu-link i.fa-chevron-down {
    transition: transform 0.3s ease;
}

.menu-link.open i.fa-chevron-down {
    transform: rotate(180deg);
}
</style>
<script>
    function toggleSubMenu(event, menuId) {
        event.preventDefault();
        const menu = document.getElementById(menuId);
        const link = event.currentTarget;
        
        if (menu.style.display === "none") {
            menu.style.display = "block";
            link.classList.add('open');
        } else {
            menu.style.display = "none";
            link.classList.remove('open');
        }
    }
</script>

    
                        <a href="{{ route('user.support') }}" class="menu-link">
                            <i class="fa-solid fa-headset"></i> সাপোর্ট টিকেট
                        </a>
                        <a href="{{ route('user.profile') }}" class="menu-link">
                            <i class="fa-solid fa-user-gear"></i> প্রোফাইল সেটিংস
                        </a>
                        <a href="{{ route('user.logout') }}" class="menu-link text-danger mt-4"><i class="fa-solid fa-power-off"></i> লগআউট</a>
                    </div>
                </div>
            </div>

            {{-- মেইন কন্টেন্ট (ডানে) --}}
            <div class="col-lg-9">
                
                {{-- ১. হোস্টিং মেইন কার্ড --}}
                <div class="premium-card">
                    <div class="service-header">
                        <div>
                            <h2 class="domain-title">{{ $hosting->domain }}</h2>
                            <p class="text-muted small mb-0">প্যাকেজ: <span class="text-primary fw-bold">{{ $hosting->plan->name ?? 'Enterprise Pro' }}</span></p>
                        </div>
                   <div>
        {{-- ডাটাবেসের স্ট্যাটাস অনুযায়ী ডায়নামিক ব্যাজ --}}
        @if($hosting->status == 'Active')
            <span class="status-badge status-active">
                <i class="fa-solid fa-circle-check me-1"></i> সচল (Active)
            </span>
        @elseif($hosting->status == 'Suspended')
            <span class="status-badge status-suspended">
                <i class="fa-solid fa-circle-exclamation me-1"></i> স্থগিত (Suspended)
            </span>
        @elseif($hosting->status == 'Expired')
            <span class="status-badge status-suspended">
                <i class="fa-solid fa-ban me-1"></i> এক্সপায়ার (Expired)
            </span>
        @else
            <span class="status-badge status-pending">
                <i class="fa-solid fa-clock me-1"></i> অপেক্ষমান ({{ $hosting->status }})
            </span>
        @endif
    </div>
                    </div>

                    {{-- সাস্পেন্ড/এক্সপায়ার হলে রিনিউ অ্যালার্ট ও পেমেন্ট অপশন --}}
                    @if($hosting->status == 'Suspended')
                        <div class="alert alert-warning border-0 shadow-sm mb-4">
                            <strong><i class="fa-solid fa-clock me-2"></i>হোস্টিং স্থগিত</strong>
                            <p class="mb-2 mt-2">মেয়াদ উত্তীর্ণ হওয়ায় হোস্টিং সাস্পেন্ড করা হয়েছে। সাপোর্টে যোগাযোগ করুন।</p>
                        </div>
                    @elseif($hosting->status == 'Expired')
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <strong><i class="fa-solid fa-ban me-2"></i>হোস্টিং এক্সপায়ার</strong>
                            <p class="mb-2 mt-2">হোস্টিং এক্সপায়ার হয়েছে। নতুন প্ল্যান কিনুন অথবা সাপোর্টে যোগাযোগ করুন।</p>
                            <a href="{{ route('user.hostings.index') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fa-solid fa-cart-plus me-1"></i> নতুন প্ল্যান কিনুন
                            </a>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-md-3 col-6">
                            <div class="info-label">সার্ভার আইপি</div>
                            <div class="info-value">{{ $hosting->ip_address ?? '103.145.110.1' }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-label">ইউজারনেম</div>
                            <div class="info-value">{{ $hosting->username ?? 'user_base' }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-label">বিলিং সাইকেল</div>
                            <div class="info-value text-capitalize">{{ $hosting->billing_cycle }}</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-label">নেক্সট ইনভয়েস</div>
                            <div class="info-value text-danger">{{ $hosting->next_due_date ? \Carbon\Carbon::parse($hosting->next_due_date)->format('d M, Y') : 'N/A' }}</div>
                        </div>
                    </div>

<div class="usage-container row">
    {{-- ডিস্ক ইউসেজ --}}
    <div class="col-md-6 mt-4">
        <div class="usage-header">
            <span>Disk Usage</span>
            <span>
                {{ round($stats['disk_used']) }} MB / 
                @if($stats['disk_limit'] === 'unlimited')
                    Unlimited
                @else
                    {{ $stats['disk_limit'] }} MB
                @endif
            </span>
        </div>
        <div class="progress progress-thin">
            <div class="progress-bar {{ $stats['disk_percent'] > 90 ? 'bg-danger' : 'bg-primary' }}" 
                 style="width: {{ $stats['disk_percent'] }}%">
            </div>
        </div>
    </div>

    {{-- ব্যান্ডউইথ (যদি সার্ভার ডাটা না দেয়, তবে প্যাকেজের ডাটা দেখাবে) --}}
    @php
        // ব্যান্ডউইথ ক্যালকুলেশন (ডাটাবেস প্ল্যান থেকে)
        $bwLimit = $hosting->plan->bandwidth ?? 'Unlimited'; 
        $bwUsed = 0; // বর্তমানে ০ রাখা হলো, লাইভ শোbw এপিআই কল না করলে এটা পাওয়া কঠিন
        $bwPercent = 5; // ডিফল্ট ৫%
    @endphp

    <div class="col-md-6 mt-4">
        <div class="usage-header">
            <span>Bandwidth (Monthly)</span>
            <span>{{ $hosting->plan->name ?? 'Standard' }} Package</span>
        </div>
        <div class="progress progress-thin">
            {{-- ব্যান্ডউইথ সাধারণত আনলিমিটেড হয় তাই এখানে শুধু একটি স্ট্যাটিক কালার রাখা হলো অথবা ৫% --}}
            <div class="progress-bar bg-success" style="width: 10%"></div>
        </div>
        <small class="text-muted" style="font-size: 10px;">
            Limit: {{ $bwLimit }} (Live stats update every 24h)
        </small>
    </div>
</div>
                </div>

                {{-- ২. কুইক ম্যানেজমেন্ট টাইলস (WHMCS/cPanel Style) --}}
                <div class="row g-4">
                    <div class="col-md-3 col-6">
                        <a href="{{ route('user.hosting.cp_login', $hosting->id) }}" target="_blank" class="action-tile">
                            <div class="tile-icon bg-soft-orange"><i class="fa-brands fa-cpanel"></i></div>
                            <span class="tile-title">cPanel</span>
                            <span class="tile-desc">কন্ট্রোল প্যানেলে অটো লগইন</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#passwordModal" class="action-tile">
                            <div class="tile-icon bg-soft-blue"><i class="fa-solid fa-key"></i></div>
                            <span class="tile-title">Password</span>
                            <span class="tile-desc">সিপ্যানেল পাসওয়ার্ড পরিবর্তন</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="https://{{ $hosting->domain }}:2096" target="_blank" class="action-tile">
                            <div class="tile-icon bg-soft-green"><i class="fa-solid fa-envelope"></i></div>
                            <span class="tile-title">Webmail</span>
                            <span class="tile-desc">ইমেইল একাউন্ট ম্যানেজ করুন</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('user.support') }}" class="action-tile">
                            <div class="tile-icon bg-soft-purple"><i class="fa-solid fa-headset"></i></div>
                            <span class="tile-title">Support</span>
                            <span class="tile-desc">সাপোর্ট টিকেট ওপেন করুন</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ================= পাসওয়ার্ড চেঞ্জ মোডাল (সুপার ক্লিন ডিজাইন) ================= --}}
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light p-4 rounded-top-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>সিকিউরিটি আপডেট</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.hosting.password.update', $hosting->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">আপনার নতুন পাসওয়ার্ডটি সরাসরি সার্ভারে আপডেট করা হবে। অন্তত ১০ ক্যারেক্টার ব্যবহার করুন।</p>
                    
                    <div class="mb-3">
                        <label class="info-label">নতুন পাসওয়ার্ড লিখুন</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="new_password" id="new_password" class="form-control border-0 bg-light py-2 shadow-none" placeholder="পাসওয়ার্ড দিন" required>
                            <button class="btn btn-light border-0" type="button" onclick="togglePass()"><i class="fa-solid fa-eye" id="eyeIcon"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow fw-bold">পাসওয়ার্ড পরিবর্তন করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePass() {
        const passInput = document.getElementById('new_password');
        const icon = document.getElementById('eyeIcon');
        if (passInput.type === "password") {
            passInput.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passInput.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'সফল!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
</script>
@endif

@endsection
