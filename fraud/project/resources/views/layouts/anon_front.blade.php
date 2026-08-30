<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @yield('meta')
  <title>{{ $gs->title ?? 'Anon - Premium eCommerce Store' }}</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ asset('assets/anon/images/logo/favicon.ico') }}" type="image/x-icon">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('assets/anon/css/style-prefix.css') }}?v=1.0">
  <link rel="stylesheet" href="{{ asset('assets/anon/css/style.css') }}?v=1.0">

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <style>
    /* Custom enhancements for Bangladeshi E-Commerce & Fraud Protection */
    .btn-quick-order {
      background: hsl(353, 100%, 65%);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: 0.2s;
    }
    .btn-quick-order:hover {
      background: hsl(353, 100%, 55%);
      color: #fff;
    }
    .currency-bdt {
      font-weight: 700;
      color: hsl(353, 100%, 65%);
    }
    .price-box {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .badge-fraud-safe {
      background: #10B981;
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 4px;
    }
    .cart-drawer-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background: hsl(353, 100%, 65%);
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
  @stack('styles')
</head>

<body>

  <div class="overlay" data-overlay></div>

  <!-- 1. NEWSLETTER MODAL -->
  <div class="modal" data-modal id="newsletterModal">
    <div class="modal-close-overlay" data-modal-overlay></div>
    <div class="modal-content">
      <button class="modal-close-btn" data-modal-close>
        <ion-icon name="close-outline"></ion-icon>
      </button>

      <div class="newsletter-img">
        <img src="{{ asset('assets/anon/images/newsletter.png') }}" alt="subscribe newsletter" width="400" height="400">
      </div>

      <div class="newsletter">
        <form action="{{ route('front.subscribers.store') }}" method="POST">
          @csrf
          <div class="newsletter-header">
            <h3 class="newsletter-title">বিশেষ অফার ও ডিসকাউন্ট পান!</h3>
            <p class="newsletter-desc">
              আমাদের <b>{{ $gs->title ?? 'Anon' }}</b> পরিবারের সাথে যুক্ত থাকুন এবং নতুন সকল প্রোডাক্টে আকর্ষণীয় ছাড় উপভোগ করুন।
            </p>
          </div>

          <input type="email" name="email" class="email-field" placeholder="আপনার ইমেইল ঠিকানা দিন" required>
          <button type="submit" class="btn-newsletter">সাবস্ক্রাইব করুন</button>
        </form>
      </div>
    </div>
  </div>

  <!-- 2. NOTIFICATION TOAST -->
  <div class="notification-toast" data-toast>
    <button class="toast-close-btn" data-toast-close>
      <ion-icon name="close-outline"></ion-icon>
    </button>

    <div class="toast-banner">
      <img src="{{ asset('assets/anon/images/products/jewellery-1.jpg') }}" alt="Product" width="80" height="70">
    </div>

    <div class="toast-detail">
      <p class="toast-message">এইমাত্র একজন গ্রাহক অর্ডার করেছেন</p>
      <p class="toast-title">চিকেন বুস্টার প্রিমিয়াম গ্রোথ প্যাক</p>
      <p class="toast-meta"><time datetime="PT2M">২ মিনিট</time> আগে (ঢাকা)</p>
    </div>
  </div>

  <!-- 3. HEADER -->
  <header>
    <div class="header-top">
      <div class="container">
        <ul class="header-social-container">
          <li>
            <a href="#" class="social-link"><ion-icon name="logo-facebook"></ion-icon></a>
          </li>
          <li>
            <a href="#" class="social-link"><ion-icon name="logo-twitter"></ion-icon></a>
          </li>
          <li>
            <a href="#" class="social-link"><ion-icon name="logo-instagram"></ion-icon></a>
          </li>
          <li>
            <a href="#" class="social-link"><ion-icon name="logo-whatsapp"></ion-icon></a>
          </li>
        </ul>

        <div class="header-alert-news">
          <p>
            <b>🚚 সারা বাংলাদেশে ক্যাশ অন ডেলিভারি</b> | জেনুইন কোয়ালিটি ও দ্রুত ডেলিভারি
          </p>
        </div>

        <div class="header-top-actions">
          <select name="currency">
            <option value="bdt">BDT ৳</option>
            <option value="usd">USD $</option>
          </select>

          <select name="language">
            <option value="bn">বাংলা (Bengali)</option>
            <option value="en">English</option>
          </select>
        </div>
      </div>
    </div>

    <div class="header-main">
      <div class="container">
        <a href="{{ route('frontend.index') }}" class="header-logo">
          <img src="{{ asset('assets/anon/images/logo/logo.svg') }}" alt="{{ $gs->title ?? 'Anon' }}" width="120" height="36">
        </a>

        <div class="header-search-container">
          <form action="{{ route('frontend.index') }}" method="GET" style="display:flex;width:100%;">
            <input type="search" name="search" class="search-field" placeholder="আপনার পছন্দের প্রোডাক্ট সার্চ করুন..." value="{{ request('search') }}">
            <button type="submit" class="search-btn">
              <ion-icon name="search-outline"></ion-icon>
            </button>
          </form>
        </div>

        <div class="header-user-actions">
          @if(auth()->check())
            <a href="{{ route('user.profile') }}" class="action-btn" title="আমার প্রোফাইল">
              <ion-icon name="person-outline"></ion-icon>
            </a>
          @else
            <a href="{{ route('login') }}" class="action-btn" title="লগইন / রেজিস্টার">
              <ion-icon name="person-outline"></ion-icon>
            </a>
          @endif

          <a href="#" class="action-btn" title="উইশলিস্ট">
            <ion-icon name="heart-outline"></ion-icon>
            <span class="count">0</span>
          </a>

          <a href="#products-section" class="action-btn" title="শপিং ব্যাগ" id="cartBtn">
            <ion-icon name="bag-handle-outline"></ion-icon>
            <span class="count" id="cartCount">0</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Navigation Menu (Desktop) -->
    <nav class="desktop-navigation-menu">
      <div class="container">
        <ul class="desktop-menu-category-list">
          <li class="menu-category">
            <a href="{{ route('frontend.index') }}" class="menu-title">হোম (Home)</a>
          </li>

          <li class="menu-category">
            <a href="#categories" class="menu-title">ক্যাটাগরি সমূহ</a>
            <div class="dropdown-panel">
              <ul class="dropdown-panel-list">
                <li class="menu-title"><a href="#">পোল্ট্রি ও এগ্রো</a></li>
                <li class="panel-list-item"><a href="#">চিকেন বুস্টার</a></li>
                <li class="panel-list-item"><a href="#">গ্রোথ প্রমোটার</a></li>
                <li class="panel-list-item"><a href="#">ভিটামিন ও মিনারেল</a></li>
              </ul>
              <ul class="dropdown-panel-list">
                <li class="menu-title"><a href="#">ফ্যাশন ও ব্যাগ</a></li>
                <li class="panel-list-item"><a href="#">প্রিমিয়াম শার্ট</a></li>
                <li class="panel-list-item"><a href="#">লেদার ব্যাগ</a></li>
                <li class="panel-list-item"><a href="#">ঘড়ি ও এক্সেসরিজ</a></li>
              </ul>
              <ul class="dropdown-panel-list">
                <li class="menu-title"><a href="#">হেলথ ও ওয়েলনেস</a></li>
                <li class="panel-list-item"><a href="#">বিপি মনিটর</a></li>
                <li class="panel-list-item"><a href="#">হেলথ সাপ্লিমেন্ট</a></li>
                <li class="panel-list-item"><a href="#">ওজন নিয়ন্ত্রণ</a></li>
              </ul>
            </div>
          </li>

          <li class="menu-category">
            <a href="#products-section" class="menu-title">হট প্রোডাক্টস</a>
          </li>

          <li class="menu-category">
            <a href="{{ route('about.us') }}" class="menu-title">আমাদের সম্পর্কে</a>
          </li>

          <li class="menu-category">
            <a href="{{ route('contact.us') }}" class="menu-title">যোগাযোগ</a>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-navigation">
      <button class="action-btn" data-mobile-menu-open-btn>
        <ion-icon name="menu-outline"></ion-icon>
      </button>

      <a href="#products-section" class="action-btn">
        <ion-icon name="bag-handle-outline"></ion-icon>
        <span class="count">0</span>
      </a>

      <a href="{{ route('frontend.index') }}" class="action-btn">
        <ion-icon name="home-outline"></ion-icon>
      </a>

      <a href="#" class="action-btn">
        <ion-icon name="heart-outline"></ion-icon>
        <span class="count">0</span>
      </a>

      <a href="{{ route('login') }}" class="action-btn">
        <ion-icon name="person-outline"></ion-icon>
      </a>
    </div>

    <!-- Mobile Navigation Menu -->
    <nav class="mobile-navigation-menu has-scrollbar" data-mobile-menu>
      <div class="menu-top">
        <h2 class="menu-title">মেন্যু</h2>
        <button class="menu-close-btn" data-mobile-menu-close-btn>
          <ion-icon name="close-outline"></ion-icon>
        </button>
      </div>

      <ul class="mobile-menu-category-list">
        <li class="menu-category">
          <a href="{{ route('frontend.index') }}" class="menu-title">হোম</a>
        </li>
        <li class="menu-category">
          <a href="#products-section" class="menu-title">সকল প্রোডাক্ট</a>
        </li>
        <li class="menu-category">
          <a href="{{ route('about.us') }}" class="menu-title">আমাদের সম্পর্কে</a>
        </li>
        <li class="menu-category">
          <a href="{{ route('contact.us') }}" class="menu-title">যোগাযোগ</a>
        </li>
      </ul>
    </nav>
  </header>

  <!-- MAIN CONTENT AREA -->
  <main>
    @yield('content')
  </main>

  <!-- 4. FOOTER -->
  <footer>
    <div class="footer-category">
      <div class="container">
        <h2 class="footer-category-title">জনপ্রিয় ব্র্যান্ড ও ক্যাটাগরি ডিরেক্টরি</h2>
        <div class="footer-category-box">
          <h3 class="category-box-title">পোল্ট্রি ও ফিড :</h3>
          <a href="#" class="footer-category-link">চিকেন বুস্টার</a>
          <a href="#" class="footer-category-link">গ্রোথ সাপ্লিমেন্ট</a>
          <a href="#" class="footer-category-link">এগ্রো কেয়ার</a>
        </div>
        <div class="footer-category-box">
          <h3 class="category-box-title">লাইফস্টাইল ও গেজেট :</h3>
          <a href="#" class="footer-category-link">স্মার্ট ওয়াচ</a>
          <a href="#" class="footer-category-link">বিপি মেশিন</a>
          <a href="#" class="footer-category-link">পারফিউম</a>
          <a href="#" class="footer-category-link">ব্যাগ কালেকশন</a>
        </div>
      </div>
    </div>

    <div class="footer-nav">
      <div class="container">
        <ul class="footer-nav-list">
          <li class="footer-nav-item"><h2 class="nav-title">জনপ্রিয় ক্যাটাগরি</h2></li>
          <li class="footer-nav-item"><a href="#" class="footer-nav-link">পোল্ট্রি ও এগ্রো</a></li>
          <li class="footer-nav-item"><a href="#" class="footer-nav-link">হেলথ কেয়ার</a></li>
          <li class="footer-nav-item"><a href="#" class="footer-nav-link">ফ্যাশন ও ক্লোথিং</a></li>
          <li class="footer-nav-item"><a href="#" class="footer-nav-link">ডিজিটাল প্রডাক্ট</a></li>
        </ul>

        <ul class="footer-nav-list">
          <li class="footer-nav-item"><h2 class="nav-title">গ্রাহক সেবা</h2></li>
          <li class="footer-nav-item"><a href="{{ route('privacy.policy') }}" class="footer-nav-link">প্রাইভেসি পলিসি</a></li>
          <li class="footer-nav-item"><a href="{{ route('terms.service') }}" class="footer-nav-link">শর্তাবলী</a></li>
          <li class="footer-nav-item"><a href="{{ route('contact.us') }}" class="footer-nav-link">যোগাযোগ</a></li>
          <li class="footer-nav-item"><a href="{{ route('about.us') }}" class="footer-nav-link">আমাদের গল্প</a></li>
        </ul>

        <ul class="footer-nav-list">
          <li class="footer-nav-item"><h2 class="nav-title">যোগাযোগ করুন</h2></li>
          <li class="footer-nav-item flex">
            <div class="icon-box"><ion-icon name="location-outline"></ion-icon></div>
            <address class="content">ঢাকা, বাংলাদেশ</address>
          </li>
          <li class="footer-nav-item flex">
            <div class="icon-box"><ion-icon name="call-outline"></ion-icon></div>
            <a href="tel:01700000000" class="footer-nav-link">01700-000000</a>
          </li>
          <li class="footer-nav-item flex">
            <div class="icon-box"><ion-icon name="mail-outline"></ion-icon></div>
            <a href="mailto:support@example.com" class="footer-nav-link">support@example.com</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <img src="{{ asset('assets/anon/images/payment.png') }}" alt="Payment methods" class="payment-img">
        <p class="copyright">
          Copyright &copy; <a href="{{ route('frontend.index') }}">{{ $gs->title ?? 'Anon Store' }}</a> সর্বস্বত্ব সংরক্ষিত।
        </p>
      </div>
    </div>
  </footer>

  <!-- IONICONS -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

  <!-- CUSTOM JS -->
  <script src="{{ asset('assets/anon/js/script.js') }}?v=1.0"></script>
  @stack('scripts')

</body>
</html>
