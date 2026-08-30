@extends('layouts.front')
@section('title', 'My Domains')
@section('contents')

{{-- ফন্ট, আইকন এবং পপ-আপ লাইব্রেরি (SweetAlert2) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #334155; }

    /* ================= আপনার আগের সাইডবার স্টাইল (হুবহু রাখা হয়েছে) ================= */
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

    /* ================= মেইন কন্টেন্ট এবং টেবিল ডিজাইন ================= */
    .main-content-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #edf2f7; }
    
    /* Stats Card with Background Colors */
    .domain-stat-box { border-radius: 15px; padding: 20px; transition: 0.3s; color: #fff; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    .stat-blue { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .stat-green { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .stat-orange { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
    
    .domain-stat-box p { color: rgba(255, 255, 255, 0.8); }
    .domain-stat-box:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

    /* Table */
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
    .custom-table thead th { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; padding: 10px 15px; border: none; }
    .custom-table tbody td { padding: 18px 15px; background: #fff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .custom-table tbody td:first-child { border-left: 1px solid #f1f5f9; border-top-left-radius: 15px; border-bottom-left-radius: 15px; }
    .custom-table tbody td:last-child { border-right: 1px solid #f1f5f9; border-top-right-radius: 15px; border-bottom-right-radius: 15px; }

    /* নেইমসার্ভার বাটন */
    .btn-manage-ns { 
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); 
        color: white; border: none; padding: 8px 16px; border-radius: 10px; 
        font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; 
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); transition: 0.3s;
    }
    .btn-manage-ns:hover { transform: scale(1.05); color: #fff; }
    .btn-manage-ns i { margin-right: 8px; font-size: 15px; color: #fbbf24; }

    /* Status Badge */
    .badge-status { padding: 5px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; }
    .status-active { background: #dcfce7; color: #15803d; }
    .status-pending { background: #fef9c3; color: #854d0e; }
    .status-expired { background: #fee2e2; color: #b91c1c; }
</style>

<div class="section-padding py-5">
    <div class="container">
        <div class="row g-4">
            
            {{-- ================= সাইডবার ================= --}}
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
                
                {{-- কালারফুল ডোমেইন সামারি কার্ডস --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="domain-stat-box stat-blue text-center shadow-sm">
                            <h3 class="fw-bold mb-0 text-white">{{ count($domains) }}</h3>
                            <p class="small mb-0">মোট ডোমেইন</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="domain-stat-box stat-green text-center shadow-sm">
                            <h3 class="fw-bold mb-0 text-white">{{ $domains->where('status', 'Active')->count() }}</h3>
                            <p class="small mb-0">সচল ডোমেইন</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="domain-stat-box stat-orange text-center shadow-sm">
                            <h3 class="fw-bold mb-0 text-white">{{ $domains->where('status', 'Pending')->count() }}</h3>
                            <p class="small mb-0">পেন্ডিং ডোমেইন</p>
                        </div>
                    </div>
                </div>

                <div class="main-content-card">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                        <h5 class="fw-bold mb-0 text-dark">আমার ডোমেইন সমূহ</h5>
                        <form action="{{ route('user.domains.index') }}" method="GET" class="d-flex">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control border-0 bg-light shadow-none ps-3" placeholder="ডোমেইন খুঁজুন..." value="{{ request('search') }}" style="border-radius: 10px 0 0 10px; width: 180px;">
                                <button class="btn btn-primary px-3" style="border-radius: 0 10px 10px 0;"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ডোমেইন ডিটেইলস</th>
                                    <th>রেজিস্ট্রেশন</th>
                                    <th>মেয়াদ শেষ</th>
                                    <th>স্ট্যাটাস</th>
                                    <th class="text-end">ম্যানেজ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($domains as $domain)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $domain->domain }}</div>
                                        <div class="small text-muted">৳ {{ number_format($domain->recurring_amount, 2) }} / বছর</div>
                                    </td>
                                    <td class="small">{{ \Carbon\Carbon::parse($domain->registration_date)->format('d M, Y') }}</td>
                                    <td>
                                        <div class="fw-bold {{ \Carbon\Carbon::parse($domain->next_due_date)->isPast() ? 'text-danger' : 'text-primary' }}">
                                            {{ \Carbon\Carbon::parse($domain->next_due_date)->format('d M, Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status status-{{ strtolower($domain->status) }}">
                                            {{ $domain->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn-manage-ns" data-bs-toggle="modal" data-bs-target="#nsModal{{ $domain->id }}">
                                            <i class="fa-solid fa-gears"></i> NS চেঞ্জ
                                        </button>
                                    </td>
                                </tr>

                                {{-- NS Update Modal --}}
                                <div class="modal fade" id="nsModal{{ $domain->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-0 bg-light rounded-top-4">
                                                <h5 class="fw-bold mb-0">নেইমসার্ভার আপডেট</h5>
                                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('user.domain.ns.update', $domain->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <p class="small text-muted mb-4 text-center">ডোমেইন: <span class="text-primary fw-bold">{{ $domain->domain }}</span></p>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold">Primary NS</label>
                                                            <input type="text" name="ns1" class="form-control bg-light border-0 shadow-none rounded-3 py-2" value="{{ $domain->ns1 }}" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold">Secondary NS</label>
                                                            <input type="text" name="ns2" class="form-control bg-light border-0 shadow-none rounded-3 py-2" value="{{ $domain->ns2 }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">সেভ করুন</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr><td colspan="5" class="text-center py-5">কোনো ডোমেইন পাওয়া যায়নি।</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                 @if ($domains->hasPages())
    <ul class="custom-pagination">
        
        {{-- ১. আগের পেজ বাটন (Previous) --}}
        @if ($domains->onFirstPage())
            <li class="disabled">
                <span><i class="fa-solid fa-chevron-left"></i></span>
            </li>
        @else
            <li>
                <a href="{{ $domains->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
            </li>
        @endif

        {{-- ২. পেজ নম্বর (লজিক: বর্তমান পেজের আশেপাশের ২টা করে পেজ দেখাবে) --}}
        @foreach(range(1, $domains->lastPage()) as $i)
            @if($i >= $domains->currentPage() - 2 && $i <= $domains->currentPage() + 2)
                @if ($i == $domains->currentPage())
                    <li class="active"><span>{{ $i }}</span></li>
                @else
                    <li><a href="{{ $domains->url($i) }}">{{ $i }}</a></li>
                @endif
            @endif
        @endforeach

        {{-- ৩. পরের পেজ বাটন (Next) --}}
        @if ($domains->hasMorePages())
            <li>
                <a href="{{ $domains->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
            </li>
        @else
            <li class="disabled">
                <span><i class="fa-solid fa-chevron-right"></i></span>
            </li>
        @endif

    </ul>
@endif
					
					
					
					
					
					
					
					<style>/* ইনলাইন কাস্টম পেজিনেশন ডিজাইন */
.custom-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    padding: 0;
    gap: 10px;
    margin-top: 30px;
}

.custom-pagination li a, 
.custom-pagination li span {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%; /* গোল বাটন */
    background: #ffffff;
    color: #555;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
}

/* হোভার ইফেক্ট */
.custom-pagination li a:hover {
    background: #fff3e0; /* হালকা অরেঞ্জ */
    color: #ff6b00; /* ব্র্যান্ড অরেঞ্জ */
    transform: translateY(-2px);
    border-color: #ff6b00;
}

/* একটিভ পেজ */
.custom-pagination li.active span {
    background: linear-gradient(135deg, #ff6b00 0%, #e65100 100%);
    color: white;
    box-shadow: 0 5px 15px rgba(255, 107, 0, 0.3);
    border: none;
}

/* ডিজেবল বাটন (Next/Prev যখন কাজ করবে না) */
.custom-pagination li.disabled span {
    background: #f8fafc;
    color: #cbd5e1;
    cursor: not-allowed;
    box-shadow: none;
}
</style>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- পপ-আপ সাকসেস মেসেজ স্ক্রিপ্ট --}}
@if(session('success'))
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    Toast.fire({
        icon: 'success',
        title: "{{ session('success') }}"
    })
</script>
@endif

<script>
    function toggleSubMenu(event, menuId) {
        event.preventDefault();
        const menu = document.getElementById(menuId);
        const icon = event.currentTarget.querySelector('.fa-chevron-down');
        if (menu.style.display === "none") {
            menu.style.display = "block";
            icon.style.transform = "rotate(180deg)";
        } else {
            menu.style.display = "none";
            icon.style.transform = "rotate(0deg)";
        }
    }
</script>

@endsection