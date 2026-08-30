@php
    $startUrl = auth()->check() ? route('user.fraud.index') : route('login');
@endphp

<footer class="site-footer">
    <div class="footer-cta-wrap">
        <div class="container">
            <div class="footer-cta">
                <div>
                    <h3>আজই শুরু করুন</h3>
                    <p>ফ্রি অ্যাকাউন্ট খুলুন এবং রিটার্ন কমাতে শুরু করুন।</p>
                </div>
                <a href="{{ $startUrl }}" class="btn btn-white btn-md">ফ্রি শুরু করুন</a>
            </div>
        </div>
    </div>

    <div class="container footer-main">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="footer-brand">
                    @if(!empty($gs->footer_logo))
                        <img src="{{ asset('assets/images/logo/'.$gs->footer_logo) }}" alt="{{ $gs->title }}" style="filter:none;height:40px;">
                    @elseif(!empty($gs->logo))
                        <img src="{{ asset('assets/images/logo/'.$gs->logo) }}" alt="{{ $gs->title }}" style="filter:none;height:40px;">
                    @else
                        <h5 class="text-white mb-3">{{ $gs->title ?? 'BD Courier' }}</h5>
                    @endif
                    <p>{{ $gs->footer_details ?? 'বাংলাদেশের #১ ই-কমার্স কাস্টমার ভেরিফিকেশন প্ল্যাটফর্ম। সকল কুরিয়ার সার্ভিসের ডেলিভারি রিপোর্ট এক জায়গায়।' }}</p>
                    <div class="social">
                        @foreach ($social_links->sortBy('id') as $social_link)
                            <a href="{{ $social_link->link }}" target="_blank" rel="noopener" aria-label="social"><i class="{{ $social_link->icon }}"></i></a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h5>প্রোডাক্ট</h5>
                <ul>
                    <li class="mb-2"><a href="{{ route('frontend.index') }}#features">ফিচার সমূহ</a></li>
                    <li class="mb-2"><a href="{{ route('frontend.index') }}#couriers">কুরিয়ার ডিরেক্টরি</a></li>
                    <li class="mb-2"><a href="{{ url('/priceing') }}">প্রাইসিং</a></li>
                    <li class="mb-2"><a href="{{ $startUrl }}">Fraud Checker</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h5>কোম্পানি</h5>
                <ul>
                    <li class="mb-2"><a href="{{ url('/about') }}">আমাদের সম্পর্কে</a></li>
                    <li class="mb-2"><a href="{{ url('/contact') }}">সাপোর্ট</a></li>
                    <li class="mb-2"><a href="{{ route('terms.service') }}">Terms of Service</a></li>
                    <li class="mb-2"><a href="{{ route('privacy.policy') }}">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <h5>যোগাযোগ</h5>
                <ul>
                    <li class="contact-li">
                        <i class="fab fa-whatsapp wa"></i>
                        <a href="https://wa.me/88{{ preg_replace('/[^0-9]/', '', $gs->phone ?? '01772411171') }}">WhatsApp: {{ $gs->phone }}</a>
                    </li>
                    <li class="contact-li">
                        <i class="fas fa-phone ph"></i>
                        <a href="tel:+88{{ preg_replace('/[^0-9]/', '', $gs->phone ?? '') }}">{{ $gs->phone }}</a>
                    </li>
                    <li class="contact-li">
                        <i class="fas fa-map-marker-alt ad"></i>
                        <span>{{ $gs->address ?? 'ঢাকা, বাংলাদেশ' }}</span>
                    </li>
                    <li class="contact-li">
                        <i class="fas fa-envelope" style="color:#c4b5fd"></i>
                        <span>{{ $gs->email }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="foot-bot">
            <p class="mb-0">&copy; {{ date('Y') }} {{ $gs->title ?? 'BD Courier' }}. All rights reserved.</p>
            <div>
                <a href="{{ route('privacy.policy') }}" class="me-3">Privacy</a>
                <a href="{{ route('terms.service') }}">Terms</a>
            </div>
        </div>
    </div>
</footer>
