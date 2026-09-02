@php
  $content = is_array($landingPage->content ?? null) ? $landingPage->content : [];
  $hdr = $content['header'] ?? [];
  $ftr = $content['footer'] ?? [];
  $chk = $content['checkout'] ?? [];
  $hero = $content['hero'] ?? [];
  $b1 = $content['benefits_section_1'] ?? [];
  $b2 = $content['benefits_section_2'] ?? [];
  $usage = $content['usage_guide'] ?? [];
  $vids = $content['video_reviews'] ?? [];
  $tstm = $content['testimonials'] ?? [];
  $faqs = $content['faqs'] ?? [];
  $offr = $content['offer_banner'] ?? [];
  $pkgs = $content['packages'] ?? [];
  $orderedSections = $sectionOrder ?? \App\Models\LandingPage::getDefaultSectionOrder();
  $enabledMap = collect($orderedSections)->pluck('enabled', 'id')->toArray();
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>{{ $landingPage->meta_title ?? $landingPage->title ?? $landingPage->name }}</title>
  <meta name="description" content="{{ $landingPage->meta_description ?? '' }}">
  <meta name="robots" content="{{ $landingPage->seo_config['robots'] ?? 'index, follow' }}">
  @if(!empty($landingPage->seo_config['canonical_url']))
    <link rel="canonical" href="{{ $landingPage->seo_config['canonical_url'] }}">
  @endif

  <!-- Social OpenGraph Meta -->
  <meta property="og:title" content="{{ $landingPage->seo_config['og_title'] ?? $landingPage->meta_title ?? $landingPage->name }}">
  <meta property="og:description" content="{{ $landingPage->seo_config['og_description'] ?? $landingPage->meta_description ?? '' }}">
  <meta property="og:image" content="{{ $landingPage->seo_config['og_image'] ?? $hdr['logo_image'] ?? '' }}">
  <meta property="og:type" content="product">

  <!-- Centralized Meta/Facebook Pixel -->
  @include('partials.meta-pixel')

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* ==========================================================================
       DYNAMIC MASTER THEME TOKENS (Chicken Booster Design System)
       ========================================================================== */
    :root {
      --brand-teal: {{ $themeConfig['primary_color'] ?? '#054c55' }};
      --brand-teal-dark: {{ $themeConfig['secondary_color'] ?? '#03363d' }};
      --brand-teal-light: #0d636f;
      --brand-teal-soft: {{ $themeConfig['light_teal'] ?? '#eaf5f6' }};

      --btn-red: {{ $themeConfig['btn_red'] ?? '#d90429' }};
      --btn-red-hover: {{ $themeConfig['btn_red_hover'] ?? '#b50322' }};
      --btn-red-shadow: rgba(217, 4, 41, 0.35);

      --accent-yellow: {{ $themeConfig['accent_yellow'] ?? '#ffd166' }};
      --accent-gold: {{ $themeConfig['accent_gold'] ?? '#f59e0b' }};

      --text-dark: {{ $themeConfig['text_dark'] ?? '#1e293b' }};
      --text-muted: {{ $themeConfig['text_muted'] ?? '#475569' }};
      --text-light: {{ $themeConfig['text_light'] ?? '#64748b' }};

      --white: #ffffff;
      --bg-light: #f8fafc;
      --border-color: {{ $themeConfig['border_color'] ?? '#e2e8f0' }};

      --font-bn: {{ $themeConfig['font_family_bn'] ?? "'Hind Siliguri', sans-serif" }};
      --font-en: {{ $themeConfig['font_family_en'] ?? "'Outfit', 'Poppins', sans-serif" }};

      --radius-sm: 6px;
      --radius-md: 10px;
      --radius-lg: 16px;
      --radius-pill: 9999px;

      --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
      --shadow-md: 0 4px 14px rgba(0,0,0,0.08);
      --shadow-lg: 0 10px 25px rgba(0,0,0,0.12);
      --shadow-teal: 0 8px 20px rgba(5, 76, 85, 0.25);
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-bn);
      color: var(--text-dark);
      background-color: {{ $themeConfig['bg_body'] ?? '#ffffff' }};
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    img { max-width: 100%; height: auto; display: block; }
    a { text-decoration: none; color: inherit; }
    button { font-family: inherit; cursor: pointer; border: none; background: none; }

    .container {
      width: 100%;
      max-width: {{ $themeConfig['container_width'] ?? '1120px' }};
      margin: 0 auto;
      padding: 0 16px;
    }

    /* 1. TOP HEADER / BRAND BAR */
    .top-header {
      background: #ffffff;
      border-bottom: 1px solid #edf2f7;
      padding: 10px 0;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .top-header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .btn-hotline {
      background: linear-gradient(180deg, #e63946 0%, var(--btn-red) 100%);
      color: #ffffff;
      font-size: 14px;
      font-weight: 700;
      padding: 9px 18px;
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
      white-space: nowrap;
      box-shadow: 0 4px 12px var(--btn-red-shadow);
      border: 1px solid rgba(255,255,255,0.15);
    }
    .btn-hotline:hover {
      background: linear-gradient(180deg, var(--btn-red) 0%, var(--btn-red-hover) 100%);
      transform: translateY(-1px);
    }
    .brand-logo-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .brand-logo-img {
      height: 56px;
      width: auto;
      max-width: 220px;
      object-fit: contain;
    }
    .btn-header-order {
      background: linear-gradient(180deg, #e63946 0%, var(--btn-red) 100%);
      color: #ffffff;
      font-size: 14px;
      font-weight: 700;
      padding: 9px 20px;
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.2s ease;
      white-space: nowrap;
      box-shadow: 0 4px 12px var(--btn-red-shadow);
      border: 1px solid rgba(255,255,255,0.15);
    }
    .btn-header-order:hover {
      background: linear-gradient(180deg, var(--btn-red) 0%, var(--btn-red-hover) 100%);
      transform: translateY(-1px);
    }
    @media (max-width: 600px) {
      .btn-hotline span.phone-text { display: none; }
      .btn-hotline { padding: 8px 12px; min-width: 40px; }
      .brand-logo-img { height: 44px; max-width: 150px; }
      .btn-header-order { padding: 8px 14px; font-size: 13px; }
    }

    /* 2. HERO SECTION */
    .hero-section {
      background: var(--brand-teal);
      color: var(--white);
      padding: 34px 0 54px;
      text-align: center;
      position: relative;
    }
    .hero-alert-hook {
      color: #ff3b3b;
      font-size: clamp(20px, 3.8vw, 30px);
      font-weight: 800;
      line-height: 1.3;
      margin-bottom: 8px;
      text-shadow: 0 2px 6px rgba(0,0,0,0.45);
    }
    .hero-main-title {
      color: var(--accent-yellow);
      font-size: clamp(22px, 4.4vw, 36px);
      font-weight: 800;
      line-height: 1.35;
      margin-bottom: 28px;
      max-width: 960px;
      margin-left: auto;
      margin-right: auto;
      text-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }
    .hero-dual-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      max-width: 980px;
      margin: 0 auto 28px;
    }
    @media (min-width: 640px) {
      .hero-dual-grid { grid-template-columns: 1fr 1fr; gap: 24px; }
      .hero-dual-grid.single-card { grid-template-columns: 1fr; max-width: 540px; }
    }
    .hero-card {
      position: relative;
      height: 400px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: 0 14px 36px rgba(0,0,0,0.4);
      display: flex;
      align-items: flex-end;
      justify-content: center;
      cursor: pointer;
      background-size: cover;
      background-position: center bottom;
      background-repeat: no-repeat;
      transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), border-color 0.35s ease, box-shadow 0.35s ease;
    }
    @media (min-width: 900px) { .hero-card { height: 440px; } }
    .hero-card:hover {
      transform: translateY(-8px);
      border-color: var(--accent-yellow);
      box-shadow: 0 22px 48px rgba(0,0,0,0.55), 0 0 25px rgba(255, 209, 102, 0.35);
    }
    .hero-card img.hero-prod-img {
      width: auto;
      max-width: 84%;
      height: 86%;
      max-height: 86%;
      object-fit: contain;
      object-position: center bottom;
      display: block;
      margin-bottom: 0;
      filter: drop-shadow(0 18px 16px rgba(0, 0, 0, 0.65)) drop-shadow(0 4px 6px rgba(0, 0, 0, 0.4));
      transition: transform 0.4s ease, filter 0.4s ease;
      z-index: 1;
    }
    .hero-card:hover img.hero-prod-img {
      transform: scale(1.06) translateY(-4px);
    }
    .hero-card-tag {
      position: absolute;
      top: 14px;
      left: 14px;
      background: rgba(5, 76, 85, 0.9);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,0.3);
      color: #ffffff;
      font-size: 13px;
      font-weight: 700;
      padding: 5px 14px;
      border-radius: var(--radius-pill);
      box-shadow: 0 4px 12px rgba(0,0,0,0.25);
      z-index: 2;
    }
    .hero-subtext-bar {
      font-size: clamp(16px, 2.5vw, 22px);
      font-weight: 700;
      color: var(--white);
      margin-bottom: 24px;
      text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }

    /* Red Action Buttons */
    .btn-red-cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(180deg, #e63946 0%, var(--btn-red) 100%);
      color: var(--white);
      font-size: clamp(16px, 2.2vw, 20px);
      font-weight: 800;
      padding: 14px 34px;
      border-radius: var(--radius-sm);
      box-shadow: 0 6px 18px var(--btn-red-shadow);
      transition: all 0.25s ease;
      cursor: pointer;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.2);
      animation: pulseCta 2s infinite;
    }
    .btn-red-cta:hover {
      background: linear-gradient(180deg, var(--btn-red) 0%, var(--btn-red-hover) 100%);
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 10px 24px var(--btn-red-shadow);
    }
    @keyframes pulseCta {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.02); }
    }

    /* Organic Wave Transition */
    .wave-divider {
      width: 100%;
      overflow: hidden;
      line-height: 0;
      background: var(--brand-teal);
      transform: rotate(180deg);
    }
    .wave-divider svg {
      position: relative;
      display: block;
      width: calc(100% + 1.3px);
      height: 48px;
    }
    .wave-divider .shape-fill { fill: #ffffff; }

    /* SECTION PILL TITLES */
    .section-pill-title {
      background: var(--brand-teal);
      color: var(--white);
      font-size: clamp(18px, 3vw, 24px);
      font-weight: 800;
      padding: 12px 28px;
      border-radius: var(--radius-sm);
      display: block;
      width: fit-content;
      max-width: 90%;
      margin: 40px auto 24px;
      text-align: center;
      box-shadow: var(--shadow-sm);
    }
    .cta-center-wrap { text-align: center; margin: 20px 0 30px; }

    /* VIDEO TESTIMONIALS */
    .video-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      max-width: 920px;
      margin: 0 auto 30px;
    }
    @media (min-width: 640px) { .video-grid { grid-template-columns: 1fr 1fr; gap: 24px; } }
    .video-card {
      background: #ffffff;
      border: 2px solid var(--border-color);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-md);
      cursor: pointer;
      position: relative;
      transition: all 0.3s ease;
    }
    .video-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: var(--brand-teal);
    }
    .video-thumb-wrap {
      position: relative;
      aspect-ratio: 16/9;
      background: #000;
      overflow: hidden;
    }
    .video-thumb-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.9;
      transition: transform 0.4s ease;
    }
    .video-card:hover .video-thumb-wrap img { transform: scale(1.05); opacity: 0.98; }
    .play-btn-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 62px;
      height: 62px;
      background: rgba(217, 4, 41, 0.95);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 18px rgba(0,0,0,0.4);
      transition: transform 0.25s ease;
    }
    .play-btn-overlay::after {
      content: '';
      display: block;
      width: 0;
      height: 0;
      border-top: 10px solid transparent;
      border-bottom: 10px solid transparent;
      border-left: 17px solid #ffffff;
      margin-left: 4px;
    }
    .video-card:hover .play-btn-overlay { transform: translate(-50%, -50%) scale(1.15); }
    .video-card-info {
      padding: 14px 16px;
      background: #fafafa;
      border-top: 1px solid var(--border-color);
    }
    .video-card-info h4 { font-size: 15px; font-weight: 700; color: var(--text-dark); line-height: 1.4; }

    /* BENEFITS BOX */
    .benefits-box {
      max-width: 920px;
      margin: 0 auto 20px;
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px 24px;
      box-shadow: var(--shadow-sm);
    }
    .benefit-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px dashed #cbd5e1;
      font-size: 15.5px;
      color: #1e293b;
      line-height: 1.5;
    }
    .benefit-item:last-child { border-bottom: none; }
    .check-bullet {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: var(--brand-teal);
      color: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 900;
      flex-shrink: 0;
      margin-top: 2px;
    }

    /* USAGE GUIDE */
    .usage-card {
      max-width: 820px;
      margin: 0 auto 20px;
      background: #ffffff;
      border: 2px solid var(--border-color);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-md);
      text-align: center;
    }
    .usage-card-img {
      width: 100%;
      max-height: 380px;
      object-fit: cover;
      background: #f1f5f9;
    }
    .usage-instruction-text {
      padding: 20px 24px;
      background: #ffffff;
      font-size: clamp(16px, 2.2vw, 19px);
      font-weight: 700;
      color: #0f172a;
      line-height: 1.6;
      border-top: 1px solid var(--border-color);
    }
    .btn-helpline-pill {
      background: var(--brand-teal);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.25);
      font-size: 16px;
      font-weight: 700;
      padding: 10px 24px;
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 14px var(--shadow-teal);
      margin-top: 12px;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .btn-helpline-pill:hover {
      background: var(--brand-teal-dark);
      transform: translateY(-2px);
    }

    /* REVIEWS SECTION */
    .reviews-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 18px;
      max-width: 920px;
      margin: 0 auto 30px;
    }
    @media (min-width: 640px) { .reviews-grid { grid-template-columns: 1fr 1fr; } }
    .review-item-card {
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .review-user-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }
    .review-user-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      background: #e2e8f0;
      flex-shrink: 0;
    }
    .review-user-info h5 { font-size: 15px; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
    .review-user-info span { font-size: 12.5px; color: var(--text-muted); }
    .review-stars { color: #f59e0b; font-size: 14px; margin-bottom: 8px; }
    .review-body-text { font-size: 14px; color: #334155; line-height: 1.6; }

    /* FAQ SECTION */
    .faq-container { max-width: 860px; margin: 0 auto 30px; }
    .faq-item {
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      margin-bottom: 10px;
      overflow: hidden;
    }
    .faq-question {
      padding: 16px 20px;
      font-weight: 700;
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      user-select: none;
      background: #fdfdfd;
    }
    .faq-question:hover { background: #f8fafc; }
    .faq-answer {
      padding: 14px 20px 18px;
      font-size: 14.5px;
      color: var(--text-muted);
      border-top: 1px solid #edf2f7;
      display: none;
      line-height: 1.6;
    }
    .faq-item.active .faq-answer { display: block; }
    .faq-item.active .faq-question { color: var(--brand-teal); }

    /* CHECKOUT / ORDER FORM */
    .checkout-section {
      background: var(--brand-teal);
      color: var(--white);
      padding: 40px 0 60px;
      margin-top: 40px;
      scroll-margin-top: 60px;
    }
    .checkout-title {
      font-size: clamp(20px, 3.2vw, 26px);
      font-weight: 800;
      text-align: center;
      margin-bottom: 30px;
      padding: 0 16px;
    }
    .checkout-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 28px;
      max-width: 980px;
      margin: 0 auto;
    }
    @media (min-width: 860px) { .checkout-grid { grid-template-columns: 1.1fr 0.9fr; gap: 32px; } }
    .form-column-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 14px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      padding-bottom: 6px;
    }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; color: var(--white); }
    .form-label span.req { color: #ff4d6d; }
    .form-control {
      width: 100%;
      height: 48px;
      padding: 10px 14px;
      font-size: 15px;
      font-family: inherit;
      border: 1px solid #cbd5e1;
      border-radius: var(--radius-sm);
      background: #ffffff;
      color: #0f172a;
      outline: none;
      transition: all 0.2s ease;
    }
    textarea.form-control { height: auto; resize: vertical; }
    .form-control:focus { border-color: var(--accent-yellow); box-shadow: 0 0 0 3px rgba(255, 209, 102, 0.3); }
    .field-error { color: #ffd166; font-size: 12.5px; margin-top: 4px; display: none; font-weight: 600; }
    .has-error .field-error { display: block; }
    .has-error .form-control { border-color: #ff4d6d; background: #fff5f5; }

    /* Product Selection Cards */
    .product-select-group { margin-top: 20px; }
    .product-option-card {
      background: rgba(255,255,255,0.08);
      border: 1.5px solid rgba(255,255,255,0.2);
      border-radius: var(--radius-sm);
      padding: 12px 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .product-option-card:hover { background: rgba(255,255,255,0.14); border-color: var(--accent-yellow); }
    .product-option-card.selected {
      background: #ffffff;
      border-color: var(--accent-yellow);
      color: var(--text-dark);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .product-option-card.selected .prod-price { color: var(--btn-red); }
    .product-option-card.selected .prod-title { color: var(--text-dark); }
    .prod-radio { width: 18px; height: 18px; accent-color: var(--btn-red); cursor: pointer; }
    .prod-thumb {
      width: 48px;
      height: 48px;
      border-radius: 6px;
      object-fit: contain;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      padding: 3px;
      flex-shrink: 0;
    }
    .prod-meta { flex: 1; }
    .prod-title { font-size: 14.5px; font-weight: 700; color: var(--white); }
    .prod-price { font-size: 14px; font-weight: 800; color: var(--accent-yellow); }

    /* Quantity Steppers */
    .prod-card-qty { margin-left: auto; flex-shrink: 0; display: inline-flex; align-items: center; }
    .qty-stepper {
      display: inline-flex;
      align-items: center;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      overflow: hidden;
      box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
    .qty-btn {
      width: 28px;
      height: 32px;
      background: #ffffff;
      border: none;
      color: #334155;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .qty-btn:hover:not(:disabled) { background: #f1f5f9; }
    .qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .qty-number {
      width: 34px;
      height: 32px;
      border: none;
      border-left: 1px solid #e2e8f0;
      border-right: 1px solid #e2e8f0;
      text-align: center;
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      background: #ffffff;
      outline: none;
    }

    /* Review Table */
    .order-review-card {
      background: #ffffff;
      color: var(--text-dark);
      border-radius: var(--radius-md);
      padding: 24px;
      box-shadow: var(--shadow-lg);
    }
    .review-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .review-table th, .review-table td { padding: 10px 4px; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
    .review-table th { text-align: left; font-weight: 700; color: var(--text-muted); }
    .review-table td.text-right { text-align: right; font-weight: 700; }
    .review-table tr.total-row td {
      font-size: 17px;
      font-weight: 800;
      color: var(--btn-red);
      border-bottom: 2px solid var(--brand-teal);
    }

    /* Delivery radio group */
    .delivery-group {
      margin: 14px 0;
      padding: 12px;
      background: #f8fafc;
      border-radius: var(--radius-sm);
      border: 1px solid #e2e8f0;
    }
    .delivery-radio-label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6px 0;
      font-size: 13.5px;
      cursor: pointer;
    }
    .delivery-radio-label input { margin-right: 8px; accent-color: var(--brand-teal); }

    .payment-badge-box {
      background: #f1f5f9;
      border-left: 4px solid var(--brand-teal);
      padding: 10px 14px;
      margin-bottom: 18px;
      border-radius: 4px;
      font-size: 13px;
      color: var(--text-muted);
      line-height: 1.5;
    }

    .privacy-standard-badge {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: var(--radius-sm);
      padding: 12px;
      margin-bottom: 18px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }
    .privacy-icon { font-size: 18px; color: #16a34a; flex-shrink: 0; }
    .privacy-text { font-size: 12px; color: #166534; line-height: 1.5; }
    .privacy-text b { display: block; font-size: 12.5px; margin-bottom: 2px; color: #14532d; }

    .btn-submit-order {
      width: 100%;
      background: linear-gradient(180deg, #e63946 0%, var(--btn-red) 100%);
      color: var(--white);
      font-size: 19px;
      font-weight: 800;
      padding: 16px;
      border-radius: var(--radius-sm);
      box-shadow: 0 6px 20px var(--btn-red-shadow);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all 0.25s ease;
      cursor: pointer;
    }
    .btn-submit-order:hover {
      background: linear-gradient(180deg, var(--btn-red) 0%, var(--btn-red-hover) 100%);
      transform: translateY(-2px);
    }
    .btn-submit-order:disabled { opacity: 0.65; cursor: not-allowed; }
    .spinner-icon {
      display: none;
      width: 22px;
      height: 22px;
      border: 3px solid rgba(255,255,255,0.3);
      border-top-color: #ffffff;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* FOOTER & FLOATING CALL */
    .site-footer {
      background: #ffffff;
      border-top: 1px solid var(--border-color);
      padding: 24px 0 80px;
      text-align: center;
      font-size: 13px;
      color: var(--text-light);
    }
    .footer-inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
    }
    @media (min-width: 600px) {
      .footer-inner { flex-direction: row; justify-content: space-between; }
    }

    .floating-helpline {
      position: fixed;
      bottom: 24px;
      right: 20px;
      z-index: 99;
      background: #25d366;
      color: #ffffff;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.25);
      transition: all 0.25s ease;
    }
    .floating-helpline:hover { transform: scale(1.1); }

    /* MODAL */
    .order-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.65);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.25s ease;
    }
    .order-modal-backdrop.active { opacity: 1; visibility: visible; }
    .order-modal-card {
      background: #ffffff;
      border-radius: var(--radius-lg);
      max-width: 480px;
      width: 100%;
      padding: 32px 24px;
      text-align: center;
      box-shadow: var(--shadow-lg);
      transform: scale(0.95);
      transition: transform 0.25s ease;
    }
    .order-modal-backdrop.active .order-modal-card { transform: scale(1); }
    .success-checkmark {
      width: 64px;
      height: 64px;
      background: #dcfce7;
      color: #16a34a;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      margin: 0 auto 16px;
      border: 2px solid #bbf7d0;
    }
  </style>
</head>
<body>

  <!-- 1. TOP HEADER -->
  <header class="top-header">
    <div class="container top-header-inner">
      <a href="tel:{{ $hdr['hotline_tel'] ?? '01864444411' }}" class="btn-hotline" title="সরাসরি কল করুন">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; flex-shrink:0;">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
        </svg>
        <span class="phone-text">{{ $hdr['hotline_phone'] ?? '01864-444411' }}</span>
      </a>

      <a href="./" class="brand-logo-wrap">
        @if(!empty($hdr['logo_image']))
          <img src="{{ $hdr['logo_image'] }}" alt="{{ $landingPage->name }}" class="brand-logo-img">
        @else
          <span style="font-size:22px; font-weight:800; color:var(--brand-teal);">Growth Agro</span>
        @endif
      </a>

      <a href="#checkout-form-section" class="btn-header-order">
        <span>{{ $hdr['cta_text'] ?? 'অর্ডার করুন' }}</span>
      </a>
    </div>
  </header>

  <!-- DYNAMIC SECTIONS RENDERING ACCORDING TO SECTION ORDER -->

  @foreach($orderedSections as $sec)
    @if(!empty($sec['enabled']))

      {{-- HERO SECTION --}}
      @if($sec['id'] === 'hero' && !empty($content['hero']))
        <section class="hero-section" id="hero-banner">
          <div class="container">
            @if(!empty($content['hero']['alert_hook']))
              <h2 class="hero-alert-hook">{{ $content['hero']['alert_hook'] }}</h2>
            @endif
            @if(!empty($content['hero']['main_title']))
              <h1 class="hero-main-title">{{ $content['hero']['main_title'] }}</h1>
            @endif

            @if(!empty($content['hero']['dual_cards']) && is_array($content['hero']['dual_cards']))
              <div class="hero-dual-grid {{ count($content['hero']['dual_cards']) === 1 ? 'single-card' : '' }}">
                @foreach($content['hero']['dual_cards'] as $card)
                  <div class="hero-card" onclick="selectAndScroll('{{ $card['variant_key'] ?? 'default' }}')" title="{{ $card['title'] ?? 'ক্লিক করে অর্ডার করুন' }}" style="@if(!empty($card['background_image'])) background-image: linear-gradient(180deg, rgba(3, 54, 61, 0.22) 0%, rgba(3, 54, 61, 0.42) 55%, rgba(0, 0, 0, 0.38) 100%), url('{{ $card['background_image'] }}'); @endif">
                    @if(!empty($card['tag']))
                      <span class="hero-card-tag">{{ $card['tag'] }}</span>
                    @endif
                    @if(!empty($card['product_image']))
                      <img src="{{ $card['product_image'] }}" alt="{{ $card['tag'] ?? 'Product' }}" class="hero-prod-img" loading="eager" fetchpriority="high">
                    @endif
                  </div>
                @endforeach
              </div>
            @endif

            @if(!empty($content['hero']['subtext']))
              <div class="hero-subtext-bar">{{ $content['hero']['subtext'] }}</div>
            @endif

            <a href="#checkout-form-section" class="btn-red-cta">
              <span>{{ $content['hero']['cta_button_text'] ?? '👉 অর্ডার করতে ক্লিক করুন' }}</span>
            </a>
          </div>
        </section>

        <!-- Wave Divider -->
        <div class="wave-divider">
          <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
          </svg>
        </div>
      @endif

      {{-- VIDEO REVIEWS --}}
      @if($sec['id'] === 'videos' && !empty($content['video_reviews']['items']))
        <section class="container">
          <h3 class="section-pill-title">{{ $content['video_reviews']['section_title'] ?? 'খামারিদের সফলতার গল্প' }}</h3>
          <div class="video-grid">
            @foreach($content['video_reviews']['items'] as $item)
              <div class="video-card" onclick="document.getElementById('checkout-form-section').scrollIntoView({behavior: 'smooth'})">
                <div class="video-thumb-wrap">
                  <img src="{{ $item['thumbnail'] }}" alt="{{ $item['title'] }}" loading="lazy">
                  <div class="play-btn-overlay"></div>
                </div>
                <div class="video-card-info">
                  <h4>{{ $item['title'] }}</h4>
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      {{-- BENEFITS CHECKLIST 1 --}}
      @if($sec['id'] === 'benefits_1' && !empty($b1['items']))
        <section class="container">
          <h3 class="section-pill-title">{{ $b1['section_title'] ?? 'কেন ব্যবহার করবেন?' }}</h3>
          <div class="cta-center-wrap">
            <a href="#checkout-form-section" class="btn-red-cta">👉 অর্ডার করতে ক্লিক করুন</a>
            <br>
            @if(!empty($b1['helpline_phone'] ?? $hdr['hotline_phone'] ?? null))
              <a href="tel:{{ $b1['helpline_tel'] ?? $hdr['hotline_tel'] ?? '01864444411' }}" class="btn-helpline-pill">
                <span>📞 {{ $b1['helpline_text'] ?? ('প্রয়োজনে কল করুন: ' . ($hdr['hotline_phone'] ?? '01864-444411')) }}</span>
              </a>
            @endif
          </div>
          <div class="benefits-box">
            @foreach($b1['items'] as $b)
              <div class="benefit-item">
                <span class="check-bullet">✓</span>
                <div>
                  <b>{{ $b['title'] ?? '' }}</b>
                  @if(!empty($b['desc'])) <span>{{ $b['desc'] }}</span> @endif
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      {{-- BENEFITS CHECKLIST 2 --}}
      @if($sec['id'] === 'benefits_2' && !empty($b2['items']))
        <section class="container">
          <h3 class="section-pill-title">{{ $b2['section_title'] ?? 'কেন ব্যবহার করবেন?' }}</h3>
          <div class="cta-center-wrap">
            <a href="#checkout-form-section" class="btn-red-cta">👉 অর্ডার করতে ক্লিক করুন</a>
            <br>
            @if(!empty($b2['helpline_phone'] ?? $hdr['hotline_phone'] ?? null))
              <a href="tel:{{ $b2['helpline_tel'] ?? $hdr['hotline_tel'] ?? '01864444411' }}" class="btn-helpline-pill">
                <span>📞 {{ $b2['helpline_text'] ?? ('প্রয়োজনে কল করুন: ' . ($hdr['hotline_phone'] ?? '01864-444411')) }}</span>
              </a>
            @endif
          </div>
          <div class="benefits-box">
            @foreach($b2['items'] as $b)
              <div class="benefit-item">
                <span class="check-bullet">✓</span>
                <div>
                  <b>{{ $b['title'] ?? '' }}</b>
                  @if(!empty($b['desc'])) <span>{{ $b['desc'] }}</span> @endif
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      {{-- USAGE & DOSAGE GUIDE --}}
      @if($sec['id'] === 'usage' && !empty($usage))
        <section class="container">
          <h3 class="section-pill-title">{{ $usage['section_title'] ?? 'ব্যবহার বিধিঃ' }}</h3>
          <div class="usage-card">
            @if(!empty($usage['image']))
              <img src="{{ $usage['image'] }}" alt="Usage guide" class="usage-card-img" loading="lazy">
            @endif
            @if(!empty($usage['instruction_text']))
              <div class="usage-instruction-text">{{ $usage['instruction_text'] }}</div>
            @endif
          </div>
          <div class="cta-center-wrap">
            <a href="#checkout-form-section" class="btn-red-cta">👉 অর্ডার করতে ক্লিক করুন</a>
            <br>
            <a href="tel:{{ $usage['helpline_tel'] ?? $hdr['hotline_tel'] ?? '01864444411' }}" class="btn-helpline-pill">
              <span>📞 {{ $usage['helpline_text'] ?? ('প্রয়োজনে কল করুন: ' . ($hdr['hotline_phone'] ?? '01864-444411')) }}</span>
            </a>
          </div>
        </section>
      @endif

      {{-- OFFER BANNER --}}
      @if($sec['id'] === 'offer' && !empty($content['offer_banner']))
        <section class="container" style="margin: 30px auto;">
          <div style="background: linear-gradient(135deg, var(--brand-teal) 0%, var(--brand-teal-dark) 100%); color:#fff; border-radius: var(--radius-md); padding: 30px 20px; text-align: center; box-shadow: var(--shadow-md);">
            @if(!empty($content['offer_banner']['badge']))
              <span style="background: var(--btn-red); color:#fff; font-size:13px; font-weight:700; padding:4px 14px; border-radius:var(--radius-pill);">{{ $content['offer_banner']['badge'] }}</span>
            @endif
            <h3 style="font-size:24px; font-weight:800; color:var(--accent-yellow); margin:12px 0 6px;">{{ $content['offer_banner']['title'] }}</h3>
            <p style="font-size:15px; max-width:600px; margin:0 auto 18px; opacity:0.9;">{{ $content['offer_banner']['subtitle'] }}</p>
            <a href="#checkout-form-section" class="btn-red-cta" style="font-size:17px; padding:12px 28px;">অর্ডার করতে ক্লিক করুন</a>
          </div>
        </section>
      @endif

      {{-- CUSTOMER REVIEWS / TESTIMONIALS --}}
      @if($sec['id'] === 'reviews' && !empty($content['testimonials']['items']))
        <section class="container">
          <h3 class="section-pill-title">{{ $content['testimonials']['section_title'] ?? 'গ্রাহকদের মতামত ও রিভিউ' }}</h3>
          <div class="reviews-grid">
            @php
              $sortedReviews = collect($content['testimonials']['items'])
                ->filter(function($r) { return !isset($r['is_active']) || $r['is_active']; })
                ->sortBy(function($r) { return $r['sort_order'] ?? 999; });
            @endphp
            @foreach($sortedReviews as $rev)
              <div class="review-item-card">
                <div>
                  <div class="review-user-row">
                    @if(!empty($rev['photo']))
                      <img src="{{ $rev['photo'] }}" alt="{{ $rev['name'] }}" class="review-user-avatar" onerror="this.src='/images/placeholder.webp'">
                    @else
                      <div class="review-user-avatar" style="display:flex;align-items:center;justify-content:center;font-size:20px;background:#e0f2fe;color:#0284c7;">👤</div>
                    @endif
                    <div class="review-user-info">
                      <h5>{{ $rev['name'] }} @if(!empty($rev['is_verified'])) <span style="color:#16a34a;font-size:12px;font-weight:700;">✓ ভেরিফাইড ক্রেতা</span> @endif</h5>
                      <span>{{ $rev['location'] ?? 'বাংলাদেশ' }} @if(!empty($rev['date'])) • {{ $rev['date'] }} @endif</span>
                    </div>
                  </div>
                  <div class="review-stars">
                    @for($i = 1; $i <= 5; $i++)
                      {{ $i <= ($rev['rating'] ?? 5) ? '★' : '☆' }}
                    @endfor
                  </div>
                  <p class="review-body-text">"{{ $rev['review_text'] }}"</p>
                </div>
                @if(!empty($rev['product_variant']))
                  <div style="margin-top:12px;font-size:12px;color:var(--brand-teal);font-weight:600;">পণ্য: {{ $rev['product_variant'] }}</div>
                @endif
              </div>
            @endforeach
          </div>
        </section>
      @endif

      {{-- FAQ SECTION --}}
      @if($sec['id'] === 'faq' && !empty($content['faqs']['items']))
        <section class="container">
          <h3 class="section-pill-title">{{ $content['faqs']['section_title'] ?? 'সাধারণ জিজ্ঞাসা (FAQ)' }}</h3>
          <div class="faq-container">
            @foreach($content['faqs']['items'] as $idx => $faq)
              <div class="faq-item {{ $idx === 0 ? 'active' : '' }}">
                <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                  <span>{{ $faq['question'] }}</span>
                  <span style="font-size:18px;">+</span>
                </div>
                <div class="faq-answer">
                  {{ $faq['answer'] }}
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      {{-- 7. CHECKOUT / ORDER FORM --}}
      @if($sec['id'] === 'checkout')
        <section class="checkout-section" id="checkout-form-section">
          <div class="container">
            <h2 class="checkout-title">{{ $content['checkout']['title'] ?? 'অর্ডার করতে আপনার সঠিক তথ্য দিয়ে নিচের ফর্মটি সম্পূর্ণ পূরণ করুন।' }}</h2>

            <div class="checkout-grid">
              <!-- Left Column: Form & Product Selection -->
              <div class="checkout-left-col">
                <h3 class="form-column-title">{{ $content['checkout']['billing_title'] ?? 'Billing details' }}</h3>

                <form id="order-form" novalidate autocomplete="off">
                  <div class="form-group" id="group-customer-name">
                    <label class="form-label" for="customer-name">আপনার নাম লিখুন <span class="req">*</span></label>
                    <input type="text" id="customer-name" class="form-control" placeholder="আপনার পুরো নাম" required maxlength="80">
                    <div class="field-error">অনুগ্রহ করে আপনার নাম লিখুন</div>
                  </div>

                  <div class="form-group" id="group-customer-address">
                    <label class="form-label" for="customer-address">আপনার সম্পূর্ণ ঠিকানা <span class="req">*</span></label>
                    <textarea id="customer-address" class="form-control" rows="3" placeholder="জেলা, থানা, গ্রাম/রোড বা এলাকার নাম" required maxlength="250"></textarea>
                    <div class="field-error">অনুগ্রহ করে আপনার বিস্তারিত ডেলিভারি ঠিকানা লিখুন</div>
                  </div>

                  <div class="form-group" id="group-customer-phone">
                    <label class="form-label" for="customer-phone">আপনার মোবাইল নাম্বারটি লিখুন <span class="req">*</span></label>
                    <input type="tel" id="customer-phone" class="form-control" placeholder="01XXXXXXXXX (১১ ডিজিট)" required maxlength="11" pattern="^01[3-9]\d{8}$">
                    <div class="field-error">সঠিক ১১ ডিজিটের মোবাইল নাম্বার দিন (যেমন: 017XXXXXXXX)</div>
                    <div id="courier-risk-badge" style="display:none; margin-top:8px;"></div>
                  </div>

                  <!-- Product Selection Packages -->
                  <div class="product-select-group">
                    <label class="form-label">পণ্য সিলেক্ট ও পরিমাণ নির্ধারণ করুন <span class="req">*</span></label>

                    @foreach($content['packages'] ?? [] as $pkg)
                      @if(!isset($pkg['is_active']) || $pkg['is_active'])
                        <div class="product-option-card {{ ($pkg['default_quantity'] ?? 0) > 0 ? 'selected' : '' }}" data-variant="{{ $pkg['id'] }}" data-price="{{ $pkg['price'] }}" data-name="{{ $pkg['name'] }}">
                          <input type="checkbox" name="product-select-cb" value="{{ $pkg['id'] }}" class="prod-radio" {{ ($pkg['default_quantity'] ?? 0) > 0 ? 'checked' : '' }}>
                          @if(!empty($pkg['image']))
                            <img src="{{ $pkg['image'] }}" alt="{{ $pkg['name'] }}" class="prod-thumb" loading="lazy">
                          @endif
                          <div class="prod-meta">
                            <div class="prod-title">{{ $pkg['name'] }}</div>
                            <div class="prod-price">৳{{ number_format($pkg['price']) }}</div>
                          </div>
                          <div class="prod-card-qty" onclick="event.stopPropagation()">
                            <div class="qty-stepper">
                              <button type="button" class="qty-btn btn-qty-minus" data-target="{{ $pkg['id'] }}" aria-label="Decrease quantity">−</button>
                              <input type="text" class="qty-number" id="qty-input-{{ $pkg['id'] }}" value="{{ $pkg['default_quantity'] ?? 0 }}" readonly>
                              <button type="button" class="qty-btn btn-qty-plus" data-target="{{ $pkg['id'] }}" aria-label="Increase quantity">+</button>
                            </div>
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                </form>
              </div>

              <!-- Right Column: Order Summary Review -->
              <div class="checkout-right-col">
                <h3 class="form-column-title">{{ $content['checkout']['summary_title'] ?? 'অর্ডারের সারসংক্ষেপ' }}</h3>

                <div class="order-review-card">
                  <table class="review-table">
                    <thead>
                      <tr>
                        <th>পণ্য</th>
                        <th class="text-right">মূল্য</th>
                      </tr>
                    </thead>
                    <tbody id="review-items-body"></tbody>
                    <tfoot>
                      <tr>
                        <td style="padding-top: 12px; font-weight: 600; color: #475569;">সাবটোটাল</td>
                        <td class="text-right" id="review-subtotal-row" style="padding-top: 12px; font-weight: 700; color: #0f172a;">৳ 0.00</td>
                      </tr>
                      <tr>
                        <td>
                          <div>ডেলিভারি চার্জ</div>
                          <div class="delivery-group">
                            <label class="delivery-radio-label">
                              <span>
                                <input type="radio" name="delivery-zone" value="inside" data-charge="{{ $deliveryConfig['delivery_type'] === 'free' ? 0 : ($deliveryConfig['charge_inside_dhaka'] ?? 0) }}">
                                {{ $deliveryConfig['inside_label'] ?? 'ঢাকার ভিতরে' }} {{ $deliveryConfig['delivery_type'] === 'free' ? '(ফ্রি)' : ('(৳' . ($deliveryConfig['charge_inside_dhaka'] ?? 0) . ')') }}
                              </span>
                            </label>
                            <label class="delivery-radio-label">
                              <span>
                                <input type="radio" name="delivery-zone" value="outside" data-charge="{{ $deliveryConfig['delivery_type'] === 'free' ? 0 : ($deliveryConfig['charge_outside_dhaka'] ?? 0) }}" checked>
                                {{ $deliveryConfig['outside_label'] ?? 'ঢাকার বাইরে' }} {{ $deliveryConfig['delivery_type'] === 'free' ? '(ফ্রি)' : ('(৳' . ($deliveryConfig['charge_outside_dhaka'] ?? 0) . ')') }}
                              </span>
                            </label>
                          </div>
                        </td>
                        <td class="text-right" id="review-delivery-charge" style="vertical-align: top; padding-top: 14px; font-weight: bold;">ফ্রি ডেলিভারি</td>
                      </tr>
                      <tr id="review-advance-row" style="display:none; color:#be123c; font-weight:700; background:#fff1f2;">
                        <td style="padding:8px 4px;">অগ্রিম ডেলিভারি চার্জ (বিকাশ/নগদ)</td>
                        <td class="text-right" id="review-advance-amount" style="padding:8px 4px;">৳ 0.00</td>
                      </tr>
                      <tr class="total-row">
                        <td>সর্বমোট বিল</td>
                        <td class="text-right" id="review-grand-total">৳ 0.00</td>
                      </tr>
                    </tfoot>
                  </table>

                  <!-- Cash On Delivery Badge -->
                  <div class="payment-badge-box" id="payment-badge-box">
                    <b>ক্যাশ অন ডেলিভারি (Cash on delivery)</b><br>
                    {{ $content['checkout']['cod_badge_text'] ?? 'পণ্য হাতে পেয়ে চেক করে সম্পূর্ণ মূল্য পরিশোধ করুন। অগ্রিম কোনো টাকা দিতে হবে না।' }}
                  </div>

                  <!-- Google & Gemini Privacy Standards Badge -->
                  <div class="privacy-standard-badge">
                    <span class="privacy-icon">🛡️</span>
                    <div class="privacy-text">
                      <b>{{ $content['checkout']['privacy_badge_heading'] ?? 'Google & Gemini Data Privacy Standard' }}</b>
                      {{ $content['checkout']['privacy_badge_text'] ?? 'আপনার তথ্য শতভাগ নিরাপদ ও এনক্রিপ্টেড। আপনার ফোন নম্বর ও ঠিকানা শুধুমাত্র কুরিয়ার ডেলিভারির কাজে সুরক্ষিতভাবে ব্যবহৃত হবে।' }}
                    </div>
                  </div>

                  <!-- Order Submit Button -->
                  <button type="button" class="btn-submit-order" id="btn-submit-order">
                    <span id="btn-order-text">{{ $content['checkout']['order_button_text'] ?? 'অর্ডার করুন' }} ৳ 0.00</span>
                    <span class="spinner-icon" id="order-spinner"></span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>
      @endif

      {{-- FOOTER --}}
      @if($sec['id'] === 'footer')
        <footer class="site-footer">
          <div class="container footer-inner">
            <div>{{ $ftr['copyright_text'] ?? ('© ' . date('Y') . ' Growth Agro. All rights reserved.') }}</div>
            <div>
              <a href="tel:{{ $ftr['helpline_phone'] ?? $hdr['hotline_tel'] ?? '01864444411' }}" style="color:var(--brand-teal); font-weight:700;">
                📞 হেল্পলাইন: {{ $ftr['helpline_phone'] ?? $hdr['hotline_phone'] ?? '01864-444411' }}
              </a>
            </div>
          </div>
        </footer>
      @endif

    @endif
  @endforeach

  <!-- Floating WhatsApp Hotline Button -->
  @if(!empty($ftr['whatsapp_phone']))
    <a href="https://wa.me/{{ $ftr['whatsapp_phone'] }}" target="_blank" class="floating-helpline" title="WhatsApp এ যোগাযোগ করুন" rel="noopener noreferrer">
      💬
    </a>
  @endif

  <!-- Growth Agro Unified Analytics Tracking -->
  <script src="/js/growth-agro-tracking.js"></script>
  <script>
  (function() {
    "use strict";

    const LANDING_PAGE_SLUG = "{{ $landingPage->slug }}";
    const LANDING_PAGE_ID = "{{ $landingPage->id }}";
    const LANDING_PAGE_NAME = "{{ addslashes($landingPage->name) }}";

    // 1. Unified Tracking: Page View Event
    if (window.GrowthAgroTracking) {
      window.GrowthAgroTracking.track('page_view', {
        entity_type: 'landing_page',
        entity_id: LANDING_PAGE_SLUG,
        page_path: window.location.pathname,
        properties: {
          landing_page_id: LANDING_PAGE_ID,
          landing_page_name: LANDING_PAGE_NAME
        }
      });
    }

    @php
      $firstPkgPrice = 0;
      if (!empty($pkgs) && is_array($pkgs)) {
          $firstPkg = reset($pkgs);
          $firstPkgPrice = (float)($firstPkg['price'] ?? 0);
      }

      $metaAddToCartSetting = \App\Models\Setting::get('landing_meta_add_to_cart_enabled', '1');
      $isMetaAddToCartActive = ($metaAddToCartSetting === null || $metaAddToCartSetting === '' || ($metaAddToCartSetting !== '0' && $metaAddToCartSetting !== 0 && $metaAddToCartSetting !== false && $metaAddToCartSetting !== 'false'));

      $metaInitiateCheckoutSetting = \App\Models\Setting::get('landing_meta_initiate_checkout_enabled', '1');
      $isMetaInitiateCheckoutActive = ($metaInitiateCheckoutSetting === null || $metaInitiateCheckoutSetting === '' || ($metaInitiateCheckoutSetting !== '0' && $metaInitiateCheckoutSetting !== 0 && $metaInitiateCheckoutSetting !== false && $metaInitiateCheckoutSetting !== 'false'));
    @endphp

    const META_ADD_TO_CART_ENABLED = {{ $isMetaAddToCartActive ? 'true' : 'false' }};
    const META_INITIATE_CHECKOUT_ENABLED = {{ $isMetaInitiateCheckoutActive ? 'true' : 'false' }};

    // 2. Catalog & Delivery Configuration
    const CATALOG = {
      @foreach($content['packages'] ?? [] as $pkg)
        "{{ $pkg['id'] }}": {
          id: "{{ $pkg['id'] }}",
          name: "{{ addslashes($pkg['name']) }}",
          price: {{ (float) $pkg['price'] }},
          weight: "{{ $pkg['weight'] ?? '' }}"
        },
      @endforeach
    };

    const DELIVERY_CONFIG = @json($deliveryConfig);

    let variantQuantities = {
      @foreach($content['packages'] ?? [] as $pkg)
        "{{ $pkg['id'] }}": {{ (int) ($pkg['default_quantity'] ?? 0) }},
      @endforeach
    };

    let currentDeliveryZone = "outside";
    let customerRiskData = null;
    let phoneCheckTimer = null;

    const optionCards = document.querySelectorAll('.product-option-card');
    const deliveryRadios = document.querySelectorAll('input[name="delivery-zone"]');
    const reviewSubtotalRow = document.getElementById('review-subtotal-row');
    const reviewDeliveryCharge = document.getElementById('review-delivery-charge');
    const reviewGrandTotal = document.getElementById('review-grand-total');
    const btnOrderText = document.getElementById('btn-order-text');
    const btnSubmit = document.getElementById('btn-submit-order');
    const orderSpinner = document.getElementById('order-spinner');

    const form = document.getElementById('order-form');
    const nameInput = document.getElementById('customer-name');
    const addressInput = document.getElementById('customer-address');
    const phoneInput = document.getElementById('customer-phone');

    if (phoneInput) {
      phoneInput.addEventListener('input', function() {
        recalculate();
      });
      phoneInput.addEventListener('blur', function() {
        recalculate();
      });
    }

    // Calculation Engine
    function recalculate() {
      const itemsBody = document.getElementById('review-items-body');
      const advanceRow = document.getElementById('review-advance-row');
      const advanceAmountEl = document.getElementById('review-advance-amount');
      const paymentBadgeBox = document.getElementById('payment-badge-box');
      let subtotalVal = 0;
      let totalQty = 0;
      let rowsHtml = '';

      Object.keys(CATALOG).forEach(key => {
        const qty = variantQuantities[key] || 0;
        const item = CATALOG[key];
        const card = document.querySelector('.product-option-card[data-variant="' + key + '"]');
        const input = document.getElementById('qty-input-' + key);
        if (input) input.value = qty;

        const cb = card ? card.querySelector('input[type="checkbox"]') : null;
        if (cb) cb.checked = (qty > 0);

        if (qty > 0) {
          if (card) card.classList.add('selected');
          subtotalVal += item.price * qty;
          totalQty += qty;
          rowsHtml += '<tr style="border-bottom: 1px solid #f1f5f9;">' +
            '<td style="padding: 10px 4px;">' +
              '<div style="font-weight: 700; font-size: 14.5px; color: #0f172a; margin-bottom: 2px;">' + item.name + '</div>' +
              '<div style="font-size: 13px; color: #64748b; font-weight: 600;">৳' + item.price.toLocaleString('en-US') + ' × ' + qty + '</div>' +
            '</td>' +
            '<td class="text-right" style="vertical-align: middle; font-size: 15px; font-weight: 700; color: #0f172a;">' +
              '৳ ' + (item.price * qty).toLocaleString('en-US') + '.00' +
            '</td>' +
          '</tr>';
        } else if (card) {
          card.classList.remove('selected');
        }
      });

      // Delivery Charge Calculation based on Admin config
      let deliveryCharge = 0;
      const isFree = (DELIVERY_CONFIG.delivery_type === 'free') ||
                     (DELIVERY_CONFIG.free_delivery_above && subtotalVal >= (DELIVERY_CONFIG.free_delivery_threshold || 1000));

      if (!isFree) {
        if (currentDeliveryZone === 'inside') {
          deliveryCharge = parseFloat(DELIVERY_CONFIG.charge_inside_dhaka || 0);
        } else {
          deliveryCharge = parseFloat(DELIVERY_CONFIG.charge_outside_dhaka || 0);
        }
      }

      if (reviewDeliveryCharge) {
        if (isFree || deliveryCharge === 0) {
          reviewDeliveryCharge.textContent = "ফ্রি ডেলিভারি";
          reviewDeliveryCharge.style.color = "#16a34a";
        } else {
          reviewDeliveryCharge.textContent = "৳ " + deliveryCharge.toLocaleString('en-US') + ".00";
          reviewDeliveryCharge.style.color = "#0f172a";
        }
      }

      const requiresAdvance = customerRiskData && customerRiskData.requires_advance;
      const advanceAmount = requiresAdvance ? (currentDeliveryZone === 'inside' ? 80 : 150) : 0;

      if (itemsBody) itemsBody.innerHTML = rowsHtml;
      if (reviewSubtotalRow) reviewSubtotalRow.textContent = "৳ " + subtotalVal.toLocaleString('en-US') + ".00";

      if (advanceRow && advanceAmountEl) {
        if (requiresAdvance) {
          advanceRow.style.display = 'table-row';
          advanceAmountEl.textContent = "৳ " + advanceAmount + ".00 (অগ্রিম)";
        } else {
          advanceRow.style.display = 'none';
        }
      }

      const grandTotal = subtotalVal + deliveryCharge;
      if (reviewGrandTotal) reviewGrandTotal.textContent = "৳ " + grandTotal.toLocaleString('en-US') + ".00";

      if (btnOrderText) {
        btnOrderText.textContent = requiresAdvance
          ? "অর্ডার কনফার্ম করুন (অগ্রিম ডেলিভারি ৳" + advanceAmount + ")"
          : "অর্ডার করুন ৳ " + grandTotal.toLocaleString('en-US') + ".00";
      }
    }

    function setVariantQuantity(variantKey, newQty) {
      if (newQty >= 0 && newQty <= 50) {
        variantQuantities[variantKey] = newQty;
        recalculate();
      }
    }

    document.querySelectorAll('.btn-qty-minus').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault(); e.stopPropagation();
        const target = btn.getAttribute('data-target');
        setVariantQuantity(target, (variantQuantities[target] || 0) - 1);
      });
    });

    document.querySelectorAll('.btn-qty-plus').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault(); e.stopPropagation();
        const target = btn.getAttribute('data-target');
        setVariantQuantity(target, (variantQuantities[target] || 0) + 1);
      });
    });

    document.querySelectorAll('.btn-qty-plus, .btn-qty-minus').forEach(btn => {
      btn.addEventListener('click', fireCheckoutStarted, { passive: true });
    });

    optionCards.forEach(card => {
      card.addEventListener('click', function() {
        fireCheckoutStarted();
        const key = this.getAttribute('data-variant');
        const currentQty = variantQuantities[key] || 0;
        variantQuantities[key] = currentQty > 0 ? 0 : 1;
        recalculate();
      });
    });

    deliveryRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        fireCheckoutStarted();
        currentDeliveryZone = this.value;
        if (customerRiskData) {
          checkPhoneRisk();
        } else {
          recalculate();
        }
      });
    });

    // AddToCart Event (Fires ONLY when user clicks order CTA buttons)
    let addToCartFired = false;
    function fireAddToCart() {
      if (addToCartFired) return;
      addToCartFired = true;

      if (META_ADD_TO_CART_ENABLED && typeof window.fbq === 'function') {
        let subtotalVal = 0;
        let totalItems = 0;
        if (typeof variantQuantities === 'object') {
          Object.keys(variantQuantities).forEach(k => {
            const q = variantQuantities[k] || 0;
            if (q > 0 && CATALOG[k]) {
              subtotalVal += CATALOG[k].price * q;
              totalItems += q;
            }
          });
        }

        const fallbackPrice = {{ (float)($firstPkgPrice ?? 0) }};
        const orderValue = subtotalVal > 0 ? subtotalVal : fallbackPrice;

        window.fbq('track', 'AddToCart', {
          content_ids: ['{{ addslashes($landingPage->product_id ?: $landingPage->slug) }}'],
          content_name: '{{ addslashes($landingPage->product_name ?: ($landingPage->title ?: $landingPage->name)) }}',
          content_type: 'product',
          @if(!empty($firstPkgPrice) && $firstPkgPrice > 0)
          value: orderValue > 0 ? orderValue : fallbackPrice,
          @endif
          currency: 'BDT',
          num_items: totalItems > 0 ? totalItems : 1
        });
      }
    }

    // Helper for interactive hero cards
    window.selectAndScroll = function(variantKey) {
      fireAddToCart();
      fireCheckoutStarted();
      if (variantKey && CATALOG[variantKey]) {
        variantQuantities[variantKey] = Math.max(1, variantQuantities[variantKey] || 0);
      }
      recalculate();
      const checkout = document.getElementById('checkout-form-section');
      if (checkout) checkout.scrollIntoView({ behavior: 'smooth' });
    };

    // Tracking CTA Clicks
    document.querySelectorAll('.btn-red-cta, .btn-header-order, a[href="#checkout-form-section"]').forEach(btn => {
      btn.addEventListener('click', function() {
        fireAddToCart();
        fireCheckoutStarted();
        if (window.GrowthAgroTracking) {
          window.GrowthAgroTracking.trackCta('btn_landing_cta', LANDING_PAGE_SLUG);
        }
      });
    });

    // Checkout Started Tracking (First-Party Analytics & Meta Pixel InitiateCheckout)
    let checkoutStartedFired = false;
    function fireCheckoutStarted() {
      if (checkoutStartedFired) return;
      checkoutStartedFired = true;

      // 1. First-Party Tracking
      if (window.GrowthAgroTracking) {
        window.GrowthAgroTracking.track('checkout_started', {
          entity_type: 'landing_page',
          entity_id: LANDING_PAGE_SLUG,
          page_path: window.location.pathname
        });
      }

      // 2. Meta Pixel: InitiateCheckout Event
      if (META_INITIATE_CHECKOUT_ENABLED && typeof window.fbq === 'function') {
        let subtotalVal = 0;
        let totalItems = 0;
        if (typeof variantQuantities === 'object') {
          Object.keys(variantQuantities).forEach(k => {
            const q = variantQuantities[k] || 0;
            if (q > 0 && CATALOG[k]) {
              subtotalVal += CATALOG[k].price * q;
              totalItems += q;
            }
          });
        }

        const fallbackPrice = {{ (float)($firstPkgPrice ?? 0) }};
        const orderValue = subtotalVal > 0 ? subtotalVal : fallbackPrice;

        window.fbq('track', 'InitiateCheckout', {
          content_ids: ['{{ addslashes($landingPage->product_id ?: $landingPage->slug) }}'],
          content_name: '{{ addslashes($landingPage->product_name ?: ($landingPage->title ?: $landingPage->name)) }}',
          content_type: 'product',
          @if(!empty($firstPkgPrice) && $firstPkgPrice > 0)
          value: orderValue > 0 ? orderValue : fallbackPrice,
          @endif
          currency: 'BDT',
          num_items: totalItems > 0 ? totalItems : 1
        });
      }
    }

    // Direct Scroll Visibility Tracking for Checkout Section
    const checkoutSectionEl = document.getElementById('checkout-form-section');
    if (checkoutSectionEl) {
      if ('IntersectionObserver' in window) {
        const checkoutObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              fireCheckoutStarted();
              checkoutObserver.disconnect();
            }
          });
        }, { threshold: 0.15 });
        checkoutObserver.observe(checkoutSectionEl);
      } else {
        const onScrollCheck = () => {
          const rect = checkoutSectionEl.getBoundingClientRect();
          if (rect.top < window.innerHeight && rect.bottom > 0) {
            fireCheckoutStarted();
            window.removeEventListener('scroll', onScrollCheck);
          }
        };
        window.addEventListener('scroll', onScrollCheck, { passive: true });
      }
    }

    if (form) {
      form.addEventListener('focusin', fireCheckoutStarted, { once: true, passive: true });
    }

    // Submit Order
    if (btnSubmit) {
      btnSubmit.addEventListener('click', async function() {
        fireCheckoutStarted();
        const name = sanitize(nameInput ? nameInput.value : '');
        const addr = sanitize(addressInput ? addressInput.value : '');
        const phone = phoneInput ? phoneInput.value.trim().replace(/[^0-9]/g, '') : '';

        let valid = true;
        if (!name || name.length < 2) {
          document.getElementById('group-customer-name')?.classList.add('has-error');
          valid = false;
        } else {
          document.getElementById('group-customer-name')?.classList.remove('has-error');
        }

        if (!addr || addr.length < 5) {
          document.getElementById('group-customer-address')?.classList.add('has-error');
          valid = false;
        } else {
          document.getElementById('group-customer-address')?.classList.remove('has-error');
        }

        if (!validatePhone(phone)) {
          document.getElementById('group-customer-phone')?.classList.add('has-error');
          valid = false;
        } else {
          document.getElementById('group-customer-phone')?.classList.remove('has-error');
        }

        if (!valid) {
          const firstErr = document.querySelector('.has-error input, .has-error textarea');
          if (firstErr) firstErr.focus();
          return;
        }

        const items = [];
        Object.keys(CATALOG).forEach(key => {
          const q = variantQuantities[key] || 0;
          if (q > 0) {
            items.push({
              productId: LANDING_PAGE_SLUG,
              variantId: key,
              quantity: q,
              name: CATALOG[key].name,
              price: CATALOG[key].price
            });
          }
        });

        if (items.length === 0) {
          alert('অনুগ্রহ করে কমপক্ষে একটি পণ্য সিলেক্ট করুন।');
          return;
        }

        const totalQuantity = items.reduce((sum, it) => sum + it.quantity, 0);
        const primaryVariant = items[0].variantId;
        const primaryName = items.map(it => it.name + ' (' + it.quantity + ' টি)').join(' + ');

        btnSubmit.disabled = true;
        if (orderSpinner) orderSpinner.style.display = 'inline-block';
        if (btnOrderText) btnOrderText.textContent = "অর্ডার প্রসেস হচ্ছে...";

        const orderPayload = {
          productId: LANDING_PAGE_SLUG,
          product_id: LANDING_PAGE_SLUG,
          variantId: primaryVariant,
          variant_id: primaryVariant,
          quantity: totalQuantity,
          items: items,
          deliveryZone: currentDeliveryZone,
          delivery_zone: currentDeliveryZone,
          customerName: name,
          customer_name: name,
          name: name,
          phone: phone,
          customer_phone: phone,
          address: addr,
          shipping_address: addr,
          source: "LANDING_PAGE",
          advance_paid: 0,
          customer: { name, phone, address: addr, delivery_zone: currentDeliveryZone },
          paymentMethod: "Cash on Delivery",
          notes: "Landing Page (" + LANDING_PAGE_SLUG + ")"
        };

        try {
          const res = await fetch('/api/orders', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify(orderPayload)
          });

          let data = null;
          try {
            data = await res.json();
          } catch (jsonErr) {
            data = null;
          }

          if (res.ok && data && data.success) {
            const order = data.order || {};
            const orderNo = order.order_number || data.order_number || ('CB-' + Date.now().toString().slice(-6));

            // Redirect immediately to source-matched dedicated order success page
            window.location.href = '/product/' + encodeURIComponent(LANDING_PAGE_SLUG) + '/success/' + encodeURIComponent(orderNo);
            return;
          } else {
            const serverMsg = (data && (data.error || data.message || (Array.isArray(data.errors) && data.errors[0])))
              ? (data.error || data.message || data.errors[0])
              : 'অনুগ্রহ করে আপনার তথ্য ও প্যাকেজ সিলেক্ট করে আবার চেষ্টা করুন।';
            alert('অর্ডার সম্পন্ন করা যায়নি: ' + serverMsg);
          }
        } catch (err) {
          console.error('[Order Error]', err);
          alert('অর্ডার সাবমিট করতে সমস্যা হয়েছে: ' + (err.message || 'সার্ভারের সাথে সংযোগ স্থাপন করা সম্ভব হয়নি।'));
        } finally {
          btnSubmit.disabled = false;
          if (orderSpinner) orderSpinner.style.display = 'none';
          recalculate();
        }
      });
    }

    // Initial recalculation
    recalculate();
  })();
  </script>
</body>
</html>
