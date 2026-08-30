@php
    $avatar = Auth::user()->photo
        ? asset('assets/images/users/'.Auth::user()->photo)
        : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
@endphp

<aside class="ud-sidebar">
    <div class="ud-profile">
        <img src="{{ $avatar }}" class="ud-avatar" alt="{{ Auth::user()->name }}">
        <h6>{{ Auth::user()->name }}</h6>
        <span class="ud-role"><i class="fas fa-shield-alt"></i> Client Account</span>
    </div>

    <nav class="ud-menu">
        <a href="{{ route('user.dashboard') }}" class="ud-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> ড্যাশবোর্ড
        </a>
        <a href="{{ route('user.fraud.index') }}" class="ud-link {{ request()->routeIs('user.fraud.index') || request()->routeIs('user.fraud.check') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i> Fraud Checker
        </a>
        <a href="{{ route('user.fraud.logs') }}" class="ud-link {{ request()->routeIs('user.fraud.logs') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Check History
        </a>
        <a href="{{ route('user.support') }}" class="ud-link {{ request()->routeIs('user.support*') || request()->routeIs('user.ticket*') ? 'active' : '' }}">
            <i class="fas fa-headset"></i> সাপোর্ট টিকেট
        </a>
        <a href="{{ route('user.profile') }}" class="ud-link {{ request()->routeIs('user.profile') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> প্রোফাইল সেটিংস
        </a>
        <a href="{{ route('user.logout') }}" class="ud-link logout">
            <i class="fas fa-sign-out-alt"></i> লগআউট
        </a>
    </nav>
</aside>
