@extends('layouts.front')
@section('title', 'My Hostings')
@section('contents')

{{-- ফন্ট, আইকন এবং পপ-আপ লাইব্রেরি (SweetAlert2) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #334155; }

    /* ================= আপনার ডোমেইন পেইজের সাইডবার স্টাইল (সেম টু সেম) ================= */
    .sidebar-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #edf2f7; position: sticky; top: 20px; }
    .user-profile-box { text-align: center; padding: 35px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; }
    .user-avatar-wrapper { position: relative; width: 100px; height: 100px; margin: 0 auto 15px; }
    .user-avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #4e73df; background-color: #eee; }
    
    .menu-link { display: flex; align-items: center; padding: 14px 20px; color: #64748b; text-decoration: none; border-radius: 12px; margin: 5px 15px; transition: 0.3s; font-weight: 500; font-size: 15px; }
    .menu-link i { width: 30px; font-size: 18px; }
    .menu-link:hover, .menu-link.active { background: #f1f5f9; color: #4e73df; }
    .menu-link.active { font-weight: 700; background: #eef2ff; color: #4e73df; }
    .menu-link.text-danger:hover { background: #fff1f2; color: #e11d48; }

    .submenu-container { background: #fdfdfd; border-radius: 12px; margin: 2px 15px; }
    .ps-5 { padding-left: 45px !important; font-size: 14px !important; }

    /* ================= মেইন কন্টেন্ট এবং কালারফুল কার্ড ডিজাইন ================= */
    .main-content-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #edf2f7; }
    
    /* কালারফুল গ্রেডিয়েন্ট স্ট্যাটস বক্স */
    .domain-stat-box { border-radius: 15px; padding: 20px; transition: 0.3s; color: #fff; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    .stat-blue { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .stat-green { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .stat-orange { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
    
    .domain-stat-box p { color: rgba(255, 255, 255, 0.85); font-weight: 500; }
    .domain-stat-box h3 { font-weight: 800; margin-bottom: 0; }
    .domain-stat-box:hover { transform: translateY(-5px); }

    /* Table Design */
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
    .custom-table thead th { color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; padding: 10px 15px; border: none; }
    
    /* Row Design */
    .custom-table tbody tr { transition: 0.3s; background-color: #f8fafc; }
    .custom-table tbody tr:hover { background-color: #eff6ff; }
    
    .custom-table tbody td { padding: 18px 15px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .custom-table tbody td:first-child { border-left: 1px solid #f1f5f9; border-top-left-radius: 15px; border-bottom-left-radius: 15px; }
    .custom-table tbody td:last-child { border-right: 1px solid #f1f5f9; border-top-right-radius: 15px; border-bottom-right-radius: 15px; }

    /* ম্যানেজ বাটন (ড্রপডাউন) */
    .btn-manage-hosting { 
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); 
        color: white; border: none; padding: 8px 16px; border-radius: 10px; 
        font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; 
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); transition: 0.3s;
    }
    .btn-manage-hosting:hover { transform: translateY(-2px); color: #fff; }
    .btn-manage-hosting i { margin-right: 8px; font-size: 15px; color: #fbbf24; }

    .badge-status { padding: 5px 14px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-active { background: #dcfce7; color: #15803d; }
    .status-pending { background: #fef9c3; color: #854d0e; }
    .status-suspended { background: #fee2e2; color: #991b1b; }
    .status-expired { background: #fecaca; color: #b91c1c; }
</style>

<div class="section-padding py-5">
    <div class="container">
        <div class="row g-4">
            
            {{-- ================= সাইডবার (ডোমেইন পেইজের মত সেম টু সেম) ================= --}}
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

            {{-- ================= মেইন কন্টেন্ট এরিয়া ================= --}}
            <div class="col-lg-9">
                
                {{-- কালারফুল হোস্টিং সামারি কার্ডস --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="domain-stat-box stat-blue text-center shadow-sm">
                            <h3 class="text-white">{{ count($hostings) }}</h3>
                            <p class="small mb-0">মোট হোস্টিং</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="domain-stat-box stat-green text-center shadow-sm">
                            <h3 class="text-white">{{ $hostings->where('status', 'Active')->count() }}</h3>
                            <p class="small mb-0">সচল সার্ভিস</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="domain-stat-box stat-orange text-center shadow-sm">
                            <h3 class="text-white">{{ $hostings->where('status', 'Pending')->count() }}</h3>
                            <p class="small mb-0">পেন্ডিং সার্ভিস</p>
                        </div>
                    </div>
                </div>

                <div class="main-content-card">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                        <h5 class="fw-bold mb-0 text-dark">আমার হোস্টিং সার্ভিস সমূহ</h5>
                        <a href="{{ route('front.hosting.category', 'Shared') }}" class="btn btn-dark btn-sm rounded-pill px-4 shadow-sm">নতুন অর্ডার</a>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ডোমেইন ও প্যাকেজ</th>
                                    <th>মূল্য</th>
                                    <th>মেয়াদ শেষ</th>
                                    <th>স্ট্যাটাস</th>
                                    <th class="text-end">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hostings as $hosting)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hosting->domain }}</div>
                                        <div class="small text-primary fw-600"><i class="fa-solid fa-cube me-1"></i> {{ $hosting->plan->name ?? 'Unknown Plan' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">৳ {{ number_format($hosting->amount, 2) }}</div>
                                        <div class="text-muted small">{{ $hosting->billing_cycle }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold {{ \Carbon\Carbon::parse($hosting->next_due_date)->isPast() ? 'text-danger' : 'text-primary' }}">
                                            {{ $hosting->next_due_date ? \Carbon\Carbon::parse($hosting->next_due_date)->format('d M, Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status status-{{ strtolower($hosting->status) }}">
                                            {{ $hosting->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown d-inline-block">
                                            <button class="btn-manage-hosting dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-gears"></i> ম্যানেজ
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('user.hosting.details', $hosting->id) }}">
                                                        <i class="fa-solid fa-circle-info me-2 text-primary"></i> সার্ভিস ডিটেইলস
                                                    </a>
                                                </li>
                                                @if($hosting->status == 'Active')
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('user.hosting.cp_login', $hosting->id) }}">
                                                        <i class="fa-solid fa-arrow-right-to-bracket me-2 text-success"></i> cPanel লগইন
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">কোনো হোস্টিং সার্ভিস পাওয়া যায়নি।</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($hostings->hasPages())
    <div class="mt-5">
        <ul class="custom-pagination">
            
            {{-- ১. আগের পেজ বাটন (Previous) --}}
            @if ($hostings->onFirstPage())
                <li class="disabled">
                    <span><i class="fa-solid fa-chevron-left"></i></span>
                </li>
            @else
                <li>
                    <a href="{{ $hostings->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                </li>
            @endif

            {{-- ২. পেজ নম্বর (বর্তমান পেজের আগে ও পরে ২টা করে দেখাবে) --}}
            @foreach(range(1, $hostings->lastPage()) as $i)
                @if($i >= $hostings->currentPage() - 2 && $i <= $hostings->currentPage() + 2)
                    @if ($i == $hostings->currentPage())
                        <li class="active"><span>{{ $i }}</span></li>
                    @else
                        <li><a href="{{ $hostings->url($i) }}">{{ $i }}</a></li>
                    @endif
                @endif
            @endforeach

            {{-- ৩. পরের পেজ বাটন (Next) --}}
            @if ($hostings->hasMorePages())
                <li>
                    <a href="{{ $hostings->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                </li>
            @else
                <li class="disabled">
                    <span><i class="fa-solid fa-chevron-right"></i></span>
                </li>
            @endif

        </ul>
    </div>
@endif
					
					
					
					<style>
					/* কাস্টম পেজিনেশন ডিজাইন */
.custom-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    padding: 0;
    gap: 8px; /* বাটনের মাঝের গ্যাপ */
    margin-top: 30px;
}

.custom-pagination li a, 
.custom-pagination li span {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%; /* গোল বাটন */
    background: #ffffff;
    color: #64748b;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

/* হোভার ইফেক্ট */
.custom-pagination li a:hover {
    background: #fff7ed; /* খুব হালকা অরেঞ্জ */
    color: #ff6b00;      /* ব্র্যান্ড অরেঞ্জ */
    border-color: #ff6b00;
    transform: translateY(-2px);
}

/* একটিভ পেজ (বর্তমান পেজ) */
.custom-pagination li.active span {
    background: #ff6b00; /* ব্র্যান্ড অরেঞ্জ */
    color: white;
    border-color: #ff6b00;
    box-shadow: 0 4px 12px rgba(255, 107, 0, 0.3);
}

/* ডিজেবল বাটন */
.custom-pagination li.disabled span {
    background: #f8fafc;
    color: #cbd5e1;
    cursor: default;
    border-color: #f1f5f9;
    box-shadow: none;
}
</style>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- সাকসেস মেসেজ স্ক্রিপ্ট --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'সফল হয়েছে!',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
</script>
@endif

<script>
    function toggleSubMenu(event, menuId) {
        event.preventDefault();
        const menu = document.getElementById(menuId);
        const icon = event.currentTarget.querySelector('.fa-chevron-down');
        if (menu.style.display === "none" || menu.style.display === "") {
            menu.style.display = "block";
            if(icon) icon.style.transform = "rotate(180deg)";
        } else {
            menu.style.display = "none";
            if(icon) icon.style.transform = "rotate(0deg)";
        }
    }
</script>

@endsection