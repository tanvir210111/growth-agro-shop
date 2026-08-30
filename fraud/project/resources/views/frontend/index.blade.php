@extends('layouts.anon_front')

@section('content')

  <!-- 1. BANNER / HERO SLIDER -->
  <div class="banner">
    <div class="container">
      <div class="slider-container has-scrollbar">

        <div class="slider-item">
          <img src="{{ asset('assets/anon/images/banner-1.jpg') }}" alt="women's latest fashion sale" class="banner-img">
          <div class="banner-content">
            <p class="banner-subtitle">চলতি সপ্তাহের বিশেষ ট্রেন্ডিং</p>
            <h2 class="banner-title">গ্রোথ বুস্টার ও লাইফস্টাইল কালেকশন</h2>
            <p class="banner-text">মাত্র <b>৳ ১,০৫০</b> থেকে শুরু</p>
            <a href="#products-section" class="banner-btn">এখনই কিনুন</a>
          </div>
        </div>

        <div class="slider-item">
          <img src="{{ asset('assets/anon/images/banner-2.jpg') }}" alt="modern trending fashion" class="banner-img">
          <div class="banner-content">
            <p class="banner-subtitle">হট ডিসকাউন্ট অফার</p>
            <h2 class="banner-title">প্রিমিয়াম কোয়ালিটি জেনুইন প্রোডাক্টস</h2>
            <p class="banner-text">সারা বাংলাদেশে <b>ক্যাশ অন ডেলিভারি</b></p>
            <a href="#products-section" class="banner-btn">প্রোডাক্ট দেখুন</a>
          </div>
        </div>

        <div class="slider-item">
          <img src="{{ asset('assets/anon/images/banner-3.jpg') }}" alt="exclusive sale" class="banner-img">
          <div class="banner-content">
            <p class="banner-subtitle">নতুন কালেকশন</p>
            <h2 class="banner-title">সেরা মূল্যে সেরা ব্র্যান্ড সামগ্রী</h2>
            <p class="banner-text"><b>৩০% পর্যন্ত</b> আকর্ষণীয় ছাড়</p>
            <a href="#products-section" class="banner-btn">অর্ডার করুন</a>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- 2. CATEGORY ACCORDION / SHOWCASE -->
  <div class="category" id="categories">
    <div class="container">
      <div class="category-item-container has-scrollbar">

        <div class="category-item">
          <div class="category-img-box">
            <img src="{{ asset('assets/anon/images/icons/dress.svg') }}" alt="dress & frock" width="30">
          </div>
          <div class="category-content-box">
            <div class="category-rating-box">
              <h3 class="category-item-title">পোল্ট্রি ও এগ্রো</h3>
              <p class="category-item-amount">(১২)</p>
            </div>
            <a href="#products-section" class="category-btn">সব দেখুন</a>
          </div>
        </div>

        <div class="category-item">
          <div class="category-img-box">
            <img src="{{ asset('assets/anon/images/icons/coat.svg') }}" alt="winter wear" width="30">
          </div>
          <div class="category-content-box">
            <div class="category-rating-box">
              <h3 class="category-item-title">ফ্যাশন ও ক্লোথিং</h3>
              <p class="category-item-amount">(৪৮)</p>
            </div>
            <a href="#products-section" class="category-btn">সব দেখুন</a>
          </div>
        </div>

        <div class="category-item">
          <div class="category-img-box">
            <img src="{{ asset('assets/anon/images/icons/glasses.svg') }}" alt="glasses & lens" width="30">
          </div>
          <div class="category-content-box">
            <div class="category-rating-box">
              <h3 class="category-item-title">হেলথ ও ওয়েলনেস</h3>
              <p class="category-item-amount">(২৪)</p>
            </div>
            <a href="#products-section" class="category-btn">সব দেখুন</a>
          </div>
        </div>

        <div class="category-item">
          <div class="category-img-box">
            <img src="{{ asset('assets/anon/images/icons/watch.svg') }}" alt="watch" width="30">
          </div>
          <div class="category-content-box">
            <div class="category-rating-box">
              <h3 class="category-item-title">স্মার্ট গ্যাজেট</h3>
              <p class="category-item-amount">(৩৫)</p>
            </div>
            <a href="#products-section" class="category-btn">সব দেখুন</a>
          </div>
        </div>

        <div class="category-item">
          <div class="category-img-box">
            <img src="{{ asset('assets/anon/images/icons/bag.svg') }}" alt="bag" width="30">
          </div>
          <div class="category-content-box">
            <div class="category-rating-box">
              <h3 class="category-item-title">ব্যাগ ও লাগেজ</h3>
              <p class="category-item-amount">(১৬)</p>
            </div>
            <a href="#products-section" class="category-btn">সব দেখুন</a>
          </div>
        </div>

        <div class="category-item">
          <div class="category-img-box">
            <img src="{{ asset('assets/anon/images/icons/perfume.svg') }}" alt="perfume" width="30">
          </div>
          <div class="category-content-box">
            <div class="category-rating-box">
              <h3 class="category-item-title">সুগন্ধি ও পারফিউম</h3>
              <p class="category-item-amount">(২৮)</p>
            </div>
            <a href="#products-section" class="category-btn">সব দেখুন</a>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- 3. MAIN PRODUCT SECTION -->
  <div class="product-container" id="products-section">
    <div class="container">

      <!-- SIDEBAR -->
      <div class="sidebar has-scrollbar" data-mobile-menu>

        <div class="sidebar-category">
          <div class="sidebar-top">
            <h2 class="sidebar-title">ক্যাটাগরি সমূহ</h2>
            <button class="sidebar-close-btn" data-mobile-menu-close-btn>
              <ion-icon name="close-outline"></ion-icon>
            </button>
          </div>

          <ul class="sidebar-menu-category-list">
            <li class="sidebar-menu-category">
              <button class="sidebar-accordion-menu" data-accordion-btn>
                <div class="menu-title-flex">
                  <img src="{{ asset('assets/anon/images/icons/dress.svg') }}" alt="clothes" width="20" height="20" class="menu-title-img">
                  <p class="menu-title">পোল্ট্রি ও এগ্রো</p>
                </div>
                <div>
                  <ion-icon name="add-outline" class="add-icon"></ion-icon>
                  <ion-icon name="remove-outline" class="remove-icon"></ion-icon>
                </div>
              </button>
              <ul class="sidebar-submenu-category-list" data-accordion>
                <li class="sidebar-submenu-category">
                  <a href="#" class="sidebar-submenu-title">
                    <p class="product-name">চিকেন বুস্টার ১ প্যাক</p>
                    <data value="300" class="stock" title="Available Stock">৫০০ গ্রাম</data>
                  </a>
                </li>
                <li class="sidebar-submenu-category">
                  <a href="#" class="sidebar-submenu-title">
                    <p class="product-name">চিকেন বুস্টার ২ প্যাক</p>
                    <data value="50" class="stock" title="Available Stock">১ কেজি</data>
                  </a>
                </li>
                <li class="sidebar-submenu-category">
                  <a href="#" class="sidebar-submenu-title">
                    <p class="product-name">চিকেন বুস্টার ৪ প্যাক</p>
                    <data value="25" class="stock" title="Available Stock">২ কেজি</data>
                  </a>
                </li>
              </ul>
            </li>

            <li class="sidebar-menu-category">
              <button class="sidebar-accordion-menu" data-accordion-btn>
                <div class="menu-title-flex">
                  <img src="{{ asset('assets/anon/images/icons/shoes.svg') }}" alt="shoes" class="menu-title-img" width="20" height="20">
                  <p class="menu-title">ফুটওয়্যার ও জুতা</p>
                </div>
                <div>
                  <ion-icon name="add-outline" class="add-icon"></ion-icon>
                  <ion-icon name="remove-outline" class="remove-icon"></ion-icon>
                </div>
              </button>
              <ul class="sidebar-submenu-category-list" data-accordion>
                <li class="sidebar-submenu-category">
                  <a href="#" class="sidebar-submenu-title">
                    <p class="product-name">স্নিকার্স</p>
                    <data value="45" class="stock" title="Available Stock">৪৫ পিস</data>
                  </a>
                </li>
                <li class="sidebar-submenu-category">
                  <a href="#" class="sidebar-submenu-title">
                    <p class="product-name">লেদার শু</p>
                    <data value="75" class="stock" title="Available Stock">৭৫ পিস</data>
                  </a>
                </li>
              </ul>
            </li>

            <li class="sidebar-menu-category">
              <button class="sidebar-accordion-menu" data-accordion-btn>
                <div class="menu-title-flex">
                  <img src="{{ asset('assets/anon/images/icons/perfume.svg') }}" alt="perfume" class="menu-title-img" width="20" height="20">
                  <p class="menu-title">সুগন্ধি ও ওয়েলনেস</p>
                </div>
                <div>
                  <ion-icon name="add-outline" class="add-icon"></ion-icon>
                  <ion-icon name="remove-outline" class="remove-icon"></ion-icon>
                </div>
              </button>
              <ul class="sidebar-submenu-category-list" data-accordion>
                <li class="sidebar-submenu-category">
                  <a href="#" class="sidebar-submenu-title">
                    <p class="product-name">ভ্যালোর পারফিউম</p>
                    <data value="100" class="stock" title="Available Stock">১০০ মিলি</data>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>

        <!-- BEST SELLERS WIDGET -->
        <div class="product-showcase">
          <h3 class="showcase-heading">সেরা বিক্রিত পণ্য</h3>
          <div class="showcase-wrapper">
            <div class="showcase-container">
              
              <div class="showcase">
                <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('চিকেন বুস্টার (Chicken Booster) — ১ প্যাক (৫০০ গ্রাম)', 1050, 1250);">
                  <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80" alt="Chicken Booster" width="75" height="75" class="showcase-img">
                </a>
                <div class="showcase-content">
                  <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার (Chicken Booster) — ১ প্যাক (৫০০ গ্রাম)', 1050, 1250);">
                    <h4 class="showcase-title">চিকেন বুস্টার — ১ প্যাক (৫০০ গ্রাম)</h4>
                  </a>
                  <div class="showcase-rating">
                    <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                  </div>
                  <div class="price-box">
                    <del>৳ ১,২৫০</del>
                    <p class="price">৳ ১,০৫০</p>
                  </div>
                </div>
              </div>

              <div class="showcase">
                <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);">
                  <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80" alt="Chicken Booster 2 Pack" width="75" height="75" class="showcase-img">
                </a>
                <div class="showcase-content">
                  <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);">
                    <h4 class="showcase-title">চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)</h4>
                  </a>
                  <div class="showcase-rating">
                    <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                  </div>
                  <div class="price-box">
                    <del>৳ ২,৪০০</del>
                    <p class="price">৳ ১,৮৫০</p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>


      <!-- PRODUCT BOX -->
      <div class="product-box">

        <!-- 3.1 MINIMAL PRODUCT LISTS (NEW ARRIVALS, TRENDING, TOP RATED) -->
        <div class="product-minimal">

          <!-- NEW ARRIVALS -->
          <div class="product-showcase">
            <h2 class="title">নতুন আগমন (New Arrivals)</h2>
            <div class="showcase-wrapper has-scrollbar">
              <div class="showcase-container">
                
                @if(isset($products) && $products->count() > 0)
                  @foreach($products->take(4) as $p)
                    <div class="showcase">
                      <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('{{ $p->name }}', {{ $p->price }}, {{ $p->price * 1.2 }});">
                        <img src="{{ $p->photo ? asset('assets/images/'.$p->photo) : asset('assets/anon/images/products/1.jpg') }}" alt="{{ $p->name }}" width="70" class="showcase-img">
                      </a>
                      <div class="showcase-content">
                        <a href="#quickOrderModal" onclick="openQuickOrder('{{ $p->name }}', {{ $p->price }}, {{ $p->price * 1.2 }});">
                          <h4 class="showcase-title">{{ Str::limit($p->name, 28) }}</h4>
                        </a>
                        <a href="#" class="showcase-category">প্রোডাক্ট</a>
                        <div class="price-box">
                          <p class="price">৳ {{ number_format($p->price, 0) }}</p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                @else
                  <div class="showcase">
                    <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('চিকেন বুস্টার (Chicken Booster) ১ প্যাক', 1050, 1250);">
                      <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80" alt="Chicken Booster" width="70" class="showcase-img">
                    </a>
                    <div class="showcase-content">
                      <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার (Chicken Booster) ১ প্যাক', 1050, 1250);">
                        <h4 class="showcase-title">চিকেন বুস্টার ১ প্যাক (৫০০ গ্রাম)</h4>
                      </a>
                      <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                      <div class="price-box">
                        <p class="price">৳ ১,০৫০</p>
                        <del>৳ ১,২৫০</del>
                      </div>
                    </div>
                  </div>

                  <div class="showcase">
                    <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('চিকেন বুস্টার ২ প্যাক কম্বো', 1850, 2400);">
                      <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80" alt="Chicken Booster 2" width="70" class="showcase-img">
                    </a>
                    <div class="showcase-content">
                      <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার ২ প্যাক কম্বো', 1850, 2400);">
                        <h4 class="showcase-title">চিকেন বুস্টার ২ প্যাক (১ কেজি)</h4>
                      </a>
                      <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                      <div class="price-box">
                        <p class="price">৳ ১,৮৫০</p>
                        <del>৳ ২,৪০০</del>
                      </div>
                    </div>
                  </div>

                  <div class="showcase">
                    <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('চিকেন বুস্টার ৪ প্যাক সুপার সেভার', 3400, 4600);">
                      <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80" alt="Chicken Booster 4" width="70" class="showcase-img">
                    </a>
                    <div class="showcase-content">
                      <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার ৪ প্যাক সুপার সেভার', 3400, 4600);">
                        <h4 class="showcase-title">চিকেন বুস্টার ৪ প্যাক (২ কেজি)</h4>
                      </a>
                      <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                      <div class="price-box">
                        <p class="price">৳ ৩,৪০০</p>
                        <del>৳ ৪,৬০০</del>
                      </div>
                    </div>
                  </div>
                @endif

              </div>
            </div>
          </div>

          <!-- TRENDING -->
          <div class="product-showcase">
            <h2 class="title">ট্রেন্ডিং আইটেম (Trending)</h2>
            <div class="showcase-wrapper has-scrollbar">
              <div class="showcase-container">
                
                <div class="showcase">
                  <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি', 1450, 1850);">
                    <img src="{{ asset('assets/anon/images/products/perfume.jpg') }}" alt="Perfume" width="70" class="showcase-img">
                  </a>
                  <div class="showcase-content">
                    <a href="#quickOrderModal" onclick="openQuickOrder('ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি', 1450, 1850);">
                      <h4 class="showcase-title">ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি</h4>
                    </a>
                    <a href="#" class="showcase-category">সুগন্ধি</a>
                    <div class="price-box">
                      <p class="price">৳ ১,৪৫০</p>
                      <del>৳ ১,৮৫০</del>
                    </div>
                  </div>
                </div>

                <div class="showcase">
                  <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('পালসকেয়ার ডিজিটাল বিপি মনিটর', 1650, 2200);">
                    <img src="{{ asset('assets/anon/images/products/watch-1.jpg') }}" alt="BP Monitor" width="70" class="showcase-img">
                  </a>
                  <div class="showcase-content">
                    <a href="#quickOrderModal" onclick="openQuickOrder('পালসকেয়ার ডিজিটাল বিপি মনিটর', 1650, 2200);">
                      <h4 class="showcase-title">পালসকেয়ার ডিজিটাল বিপি মনিটর</h4>
                    </a>
                    <a href="#" class="showcase-category">হেলথ কেয়ার</a>
                    <div class="price-box">
                      <p class="price">৳ ১,৬৫০</p>
                      <del>৳ ২,২০০</del>
                    </div>
                  </div>
                </div>

                <div class="showcase">
                  <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('রয়েল পোলো ১০০% কটন টি-শার্ট', 650, 950);">
                    <img src="{{ asset('assets/anon/images/products/clothes-1.jpg') }}" alt="Polo Shirt" width="70" class="showcase-img">
                  </a>
                  <div class="showcase-content">
                    <a href="#quickOrderModal" onclick="openQuickOrder('রয়েল পোলো ১০০% কটন টি-শার্ট', 650, 950);">
                      <h4 class="showcase-title">রয়েল পোলো ১০০% কটন টি-শার্ট</h4>
                    </a>
                    <a href="#" class="showcase-category">মেনস ফ্যাশন</a>
                    <div class="price-box">
                      <p class="price">৳ ৬৫০</p>
                      <del>৳ ৯৫০</del>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- TOP RATED -->
          <div class="product-showcase">
            <h2 class="title">শীর্ষ রেটিংপ্রাপ্ত পণ্য</h2>
            <div class="showcase-wrapper has-scrollbar">
              <div class="showcase-container">
                
                <div class="showcase">
                  <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('চিকেন বুস্টার ৪ প্যাক (২ কেজি)', 3400, 4600);">
                    <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80" alt="Chicken Booster 4" width="70" class="showcase-img">
                  </a>
                  <div class="showcase-content">
                    <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার ৪ প্যাক (২ কেজি)', 3400, 4600);">
                      <h4 class="showcase-title">চিকেন বুস্টার ৪ প্যাক (২ কেজি)</h4>
                    </a>
                    <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                    <div class="price-box">
                      <p class="price">৳ ৩,৪০০</p>
                      <del>৳ ৪,৬০০</del>
                    </div>
                  </div>
                </div>

                <div class="showcase">
                  <a href="#quickOrderModal" class="showcase-img-box" onclick="openQuickOrder('প্রিমিয়াম লেদার ট্রাভেল ব্যাগ', 1250, 1850);">
                    <img src="{{ asset('assets/anon/images/products/party-wear-1.jpg') }}" alt="Leather Bag" width="70" class="showcase-img">
                  </a>
                  <div class="showcase-content">
                    <a href="#quickOrderModal" onclick="openQuickOrder('প্রিমিয়াম লেদার ট্রাভেল ব্যাগ', 1250, 1850);">
                      <h4 class="showcase-title">প্রিমিয়াম লেদার ট্রাভেল ব্যাগ</h4>
                    </a>
                    <a href="#" class="showcase-category">ব্যাগ ও ফ্যাশন</a>
                    <div class="price-box">
                      <p class="price">৳ ১,২৫০</p>
                      <del>৳ ১,৮৫০</del>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

        </div>


        <!-- 3.2 DEAL OF THE DAY -->
        <div class="product-featured">
          <h2 class="title">আজকের সেরা ডিল (Deal of the day)</h2>
          <div class="showcase-wrapper has-scrollbar">
            <div class="showcase-container">
              <div class="showcase">
                
                <div class="showcase-banner">
                  <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=450&auto=format&fit=crop&q=80" alt="Chicken Booster Growth Pack" class="showcase-img">
                </div>

                <div class="showcase-content">
                  <div class="showcase-rating">
                    <ion-icon name="star"></ion-icon>
                    <ion-icon name="star"></ion-icon>
                    <ion-icon name="star"></ion-icon>
                    <ion-icon name="star"></ion-icon>
                    <ion-icon name="star"></ion-icon>
                  </div>

                  <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার (Chicken Booster) — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);">
                    <h3 class="showcase-title">চিকেন বুস্টার (Chicken Booster) — দ্রুত ওজন বৃদ্ধি ও স্বাস্থ্য সুরক্ষাকারী সাপ্লিমেন্ট</h3>
                  </a>

                  <p class="showcase-desc">
                    ব্রয়লার, লেয়ার, সোনালী ও দেশি মুরগির দ্রুত ওজন বৃদ্ধি, এফসিআর (FCR) উন্নতকরণ ও রোগ প্রতিরোধ ক্ষমতা বাড়ানোর ১০০% কার্যকর ও পরীক্ষিত ফর্মুলা।
                  </p>

                  <div class="price-box">
                    <p class="price">৳ ১,৮৫০</p>
                    <del>৳ ২,৪০০</del>
                  </div>

                  <button type="button" class="add-cart-btn" onclick="openQuickOrder('চিকেন বুস্টার (Chicken Booster) — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);" style="cursor:pointer;">
                    🛒 এখনই সরাসরি অর্ডার করুন
                  </button>

                  <div class="showcase-status">
                    <div class="wrapper">
                      <p>ইতিমধ্যে বিক্রি হয়েছে: <b>৪২০ প্যাক</b></p>
                      <p>স্টক বাকি: <b>৮০ প্যাক</b></p>
                    </div>
                    <div class="showcase-status-bar" style="background:#E2E8F0;height:6px;border-radius:4px;overflow:hidden;">
                      <div style="background:hsl(353, 100%, 65%);width:84%;height:100%;"></div>
                    </div>
                  </div>

                  <div class="countdown-box">
                    <p class="countdown-desc">অফারের সময় বাকি আছে:</p>
                    <div class="countdown">
                      <div class="countdown-content"><p class="display-number">০২</p><p class="display-text">দিন</p></div>
                      <div class="countdown-content"><p class="display-number">১৪</p><p class="display-text">ঘণ্টা</p></div>
                      <div class="countdown-content"><p class="display-number">২৫</p><p class="display-text">মিনিট</p></div>
                      <div class="countdown-content"><p class="display-number">৩৮</p><p class="display-text">সেকেন্ড</p></div>
                    </div>
                  </div>

                </div>

              </div>
            </div>
          </div>
        </div>


        <!-- 3.3 PRODUCT GRID -->
        <div class="product-main">
          <h2 class="title">সকল প্রিমিয়াম প্রোডাক্ট</h2>

          <div class="product-grid">

            <!-- Card 1 -->
            <div class="showcase">
              <div class="showcase-banner">
                <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=350&auto=format&fit=crop&q=80" alt="Chicken Booster 1 Pack" width="300" class="product-img default">
                <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=350&auto=format&fit=crop&q=80" alt="Chicken Booster 1 Pack" width="300" class="product-img hover">
                <p class="showcase-badge angle pink">১৬% ছাড়</p>
                <div class="showcase-actions">
                  <button class="btn-action" onclick="openQuickOrder('চিকেন বুস্টার — ১ প্যাক (৫০০ গ্রাম)', 1050, 1250);"><ion-icon name="bag-handle-outline"></ion-icon></button>
                </div>
              </div>
              <div class="showcase-content">
                <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার — ১ প্যাক (৫০০ গ্রাম)', 1050, 1250);">
                  <h3 class="showcase-title">চিকেন বুস্টার — ১ প্যাক (৫০০ গ্রাম)</h3>
                </a>
                <div class="showcase-rating">
                  <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                </div>
                <div class="price-box">
                  <p class="price">৳ ১,০৫০</p>
                  <del>৳ ১,২৫০</del>
                </div>
                <button type="button" class="btn-quick-order" style="width:100%;margin-top:8px;justify-content:center;" onclick="openQuickOrder('চিকেন বুস্টার — ১ প্যাক (৫০০ গ্রাম)', 1050, 1250);">
                  অর্ডার করুন ➔
                </button>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="showcase">
              <div class="showcase-banner">
                <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=350&auto=format&fit=crop&q=80" alt="Chicken Booster 2 Pack" width="300" class="product-img default">
                <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=350&auto=format&fit=crop&q=80" alt="Chicken Booster 2 Pack" width="300" class="product-img hover">
                <p class="showcase-badge angle black">বেস্ট সেলার</p>
                <div class="showcase-actions">
                  <button class="btn-action" onclick="openQuickOrder('চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);"><ion-icon name="bag-handle-outline"></ion-icon></button>
                </div>
              </div>
              <div class="showcase-content">
                <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);">
                  <h3 class="showcase-title">চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)</h3>
                </a>
                <div class="showcase-rating">
                  <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                </div>
                <div class="price-box">
                  <p class="price">৳ ১,৮৫০</p>
                  <del>৳ ২,৪০০</del>
                </div>
                <button type="button" class="btn-quick-order" style="width:100%;margin-top:8px;justify-content:center;" onclick="openQuickOrder('চিকেন বুস্টার — ২ প্যাক কম্বো (১ কেজি)', 1850, 2400);">
                  অর্ডার করুন ➔
                </button>
              </div>
            </div>

            <!-- Card 3 -->
            <div class="showcase">
              <div class="showcase-banner">
                <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=350&auto=format&fit=crop&q=80" alt="Chicken Booster 4 Pack" width="300" class="product-img default">
                <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=350&auto=format&fit=crop&q=80" alt="Chicken Booster 4 Pack" width="300" class="product-img hover">
                <p class="showcase-badge angle pink">২৬% ছাড়</p>
                <div class="showcase-actions">
                  <button class="btn-action" onclick="openQuickOrder('চিকেন বুস্টার — ৪ প্যাক সুপার সেভার (২ কেজি)', 3400, 4600);"><ion-icon name="bag-handle-outline"></ion-icon></button>
                </div>
              </div>
              <div class="showcase-content">
                <a href="#" class="showcase-category">পোল্ট্রি ও এগ্রো</a>
                <a href="#quickOrderModal" onclick="openQuickOrder('চিকেন বুস্টার — ৪ প্যাক সুপার সেভার (২ কেজি)', 3400, 4600);">
                  <h3 class="showcase-title">চিকেন বুস্টার — ৪ প্যাক সুপার সেভার (২ কেজি)</h3>
                </a>
                <div class="showcase-rating">
                  <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                </div>
                <div class="price-box">
                  <p class="price">৳ ৩,৪০০</p>
                  <del>৳ ৪,৬০০</del>
                </div>
                <button type="button" class="btn-quick-order" style="width:100%;margin-top:8px;justify-content:center;" onclick="openQuickOrder('চিকেন বুস্টার — ৪ প্যাক সুপার সেভার (২ কেজি)', 3400, 4600);">
                  অর্ডার করুন ➔
                </button>
              </div>
            </div>

            <!-- Card 4 -->
            <div class="showcase">
              <div class="showcase-banner">
                <img src="{{ asset('assets/anon/images/products/perfume.jpg') }}" alt="Velour Perfume" width="300" class="product-img default">
                <img src="{{ asset('assets/anon/images/products/perfume.jpg') }}" alt="Velour Perfume" width="300" class="product-img hover">
                <div class="showcase-actions">
                  <button class="btn-action" onclick="openQuickOrder('ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি', 1450, 1850);"><ion-icon name="bag-handle-outline"></ion-icon></button>
                </div>
              </div>
              <div class="showcase-content">
                <a href="#" class="showcase-category">সুগন্ধি</a>
                <a href="#quickOrderModal" onclick="openQuickOrder('ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি', 1450, 1850);">
                  <h3 class="showcase-title">ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি</h3>
                </a>
                <div class="showcase-rating">
                  <ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon><ion-icon name="star"></ion-icon>
                </div>
                <div class="price-box">
                  <p class="price">৳ ১,৪৫০</p>
                  <del>৳ ১,৮৫০</del>
                </div>
                <button type="button" class="btn-quick-order" style="width:100%;margin-top:8px;justify-content:center;" onclick="openQuickOrder('ভ্যালোর প্রিমিয়াম পারফিউম ১০০ মিলি', 1450, 1850);">
                  অর্ডার করুন ➔
                </button>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </div>


  <!-- 4. TESTIMONIALS, SERVICES & CTA -->
  <div>
    <div class="container">
      <div class="testimonials-box">

        <!-- TESTIMONIALS -->
        <div class="testimonial">
          <h2 class="title">গ্রাহকদের প্রতিক্রিয়া</h2>
          <div class="testimonial-card">
            <img src="{{ asset('assets/anon/images/testimonial-1.jpg') }}" alt="alan doe" class="testimonial-banner" width="80" height="80">
            <p class="testimonial-name">মোঃ রফিকুল ইসলাম</p>
            <p class="testimonial-title">খামারী ও পোল্ট্রি উদ্যোক্তা, গাজীপুর</p>
            <img src="{{ asset('assets/anon/images/icons/quotes.svg') }}" alt="quotation" class="quotation-img" width="26">
            <p class="testimonial-desc">
              "চিকেন বুস্টার ব্যবহারের পর মুরগির ওজন খুব দ্রুত বেড়েছে এবং এফসিআর অনেক ভালো পাওয়া গেছে। কোয়ালিটি ও সার্ভিস সত্যিই অসাধারণ!"
            </p>
          </div>
        </div>

        <!-- CTA BANNER -->
        <div class="cta-container">
          <img src="{{ asset('assets/anon/images/cta-banner.jpg') }}" alt="summer collection" class="cta-banner">
          <a href="#products-section" class="cta-content">
            <p class="discount">২৫% ছাড়</p>
            <h2 class="cta-title">গ্রীষ্মকালীন স্পেশাল অফার</h2>
            <p class="cta-text">সীমিত সময়ের জন্য উপলব্ধ</p>
            <button class="cta-btn">অর্ডার বুক করুন</button>
          </a>
        </div>

        <!-- SERVICES -->
        <div class="service">
          <h2 class="title">আমাদের প্রতিশ্রুতি</h2>
          <div class="service-container">

            <a href="#" class="service-item">
              <div class="service-icon"><ion-icon name="boat-outline"></ion-icon></div>
              <div class="service-content">
                <h3 class="service-title">সারা বাংলাদেশে ডেলিভারি</h3>
                <p class="service-desc">দ্রুত ও নিরাপদ ডেলিভারি ব্যবস্থা</p>
              </div>
            </a>

            <a href="#" class="service-item">
              <div class="service-icon"><ion-icon name="rocket-outline"></ion-icon></div>
              <div class="service-content">
                <h3 class="service-title">ক্যাশ অন ডেলিভারি</h3>
                <p class="service-desc">পণ্য হাতে পেয়ে মূল্য পরিশোধ</p>
              </div>
            </a>

            <a href="#" class="service-item">
              <div class="service-icon"><ion-icon name="call-outline"></ion-icon></div>
              <div class="service-content">
                <h3 class="service-title">২৪/৭ কাস্টমার সাপোর্ট</h3>
                <p class="service-desc">যে কোনো প্রয়োজনে সরাসরি সহায়তা</p>
              </div>
            </a>

            <a href="#" class="service-item">
              <div class="service-icon"><ion-icon name="shield-checkmark-outline"></ion-icon></div>
              <div class="service-content">
                <h3 class="service-title">১০০% জেনুইন প্রোডাক্ট</h3>
                <p class="service-desc">গুণগত মান শতভাগ নিশ্চিত</p>
              </div>
            </a>

          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- 5. DIRECT QUICK ORDER MODAL -->
  <div id="quickOrderModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:92%;max-width:500px;padding:24px;position:relative;max-height:90vh;overflow-y:auto;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
      
      <button type="button" onclick="closeQuickOrder();" style="position:absolute;top:14px;right:14px;background:none;border:none;font-size:22px;cursor:pointer;color:#718096;">✕</button>

      <div style="text-align:center;margin-bottom:16px;">
        <h3 style="font-size:18px;font-weight:800;color:#1A202C;">🛍️ ক্যাশ অন ডেলিভারিতে দ্রুত অর্ডার করুন</h3>
        <p style="font-size:13px;color:#718096;margin-top:4px;">পণ্য হাতে পেয়ে নিশ্চিত হয়ে টাকা পরিশোধ করুন</p>
      </div>

      <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;margin-bottom:16px;">
        <div style="font-size:14px;font-weight:700;color:#2D3748;" id="modalProductName">চিকেন বুস্টার</div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
          <span style="font-size:13px;color:#718096;">মূল্য:</span>
          <span style="font-size:16px;font-weight:800;color:hsl(353, 100%, 65%);" id="modalProductPrice">৳ ১,০৫০</span>
        </div>
      </div>

      <form id="directOrderForm" onsubmit="handleDirectOrder(event);">
        <input type="hidden" id="orderItemName" value="">
        <input type="hidden" id="orderItemPrice" value="0">

        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;color:#2D3748;">আপনার নাম *</label>
          <input type="text" id="custName" required placeholder="আপনার পুরো নাম লিখুন" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:14px;">
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;color:#2D3748;">মোবাইল নম্বর (১১ ডিজিট) *</label>
          <input type="tel" id="custPhone" required placeholder="017XXXXXXXX" pattern="01[3-9][0-9]{8}" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:14px;">
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;color:#2D3748;">সম্পূর্ণ ঠিকানা *</label>
          <textarea id="custAddress" required placeholder="গ্রাম/রোড, থানা, জেলা উল্লেখ করুন" rows="2" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:14px;"></textarea>
        </div>

        <div style="display:flex;gap:10px;align-items:center;margin-bottom:16px;">
          <label style="font-size:13px;font-weight:600;color:#2D3748;">পরিমাণ (Quantity):</label>
          <select id="custQty" style="padding:6px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:14px;" onchange="updateModalTotal();">
            <option value="1">১ প্যাক</option>
            <option value="2">২ প্যাক</option>
            <option value="3">৩ প্যাক</option>
            <option value="4">৪ প্যাক</option>
          </select>
        </div>

        <div style="background:#FFF5F5;border:1px dashed hsl(353, 100%, 65%);border-radius:8px;padding:10px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:14px;font-weight:700;color:#2D3748;">সর্বমোট পরিশোধযোগ্য:</span>
          <span style="font-size:18px;font-weight:800;color:hsl(353, 100%, 65%);" id="modalFinalTotal">৳ ১,০৫০</span>
        </div>

        <button type="submit" id="submitOrderBtn" style="width:100%;padding:12px;background:hsl(353, 100%, 65%);color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:800;cursor:pointer;transition:0.2s;">
          অর্ডার নিশ্চিত করুন ➔
        </button>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  let currentProductPrice = 0;

  window.openQuickOrder = function(title, price, oldPrice) {
    currentProductPrice = price;
    document.getElementById('modalProductName').textContent = title;
    document.getElementById('orderItemName').value = title;
    document.getElementById('orderItemPrice').value = price;
    document.getElementById('modalProductPrice').textContent = '৳ ' + Number(price).toLocaleString();
    updateModalTotal();
    const modal = document.getElementById('quickOrderModal');
    modal.style.display = 'flex';
  };

  window.closeQuickOrder = function() {
    const modal = document.getElementById('quickOrderModal');
    modal.style.display = 'none';
  };

  window.updateModalTotal = function() {
    const qty = parseInt(document.getElementById('custQty').value) || 1;
    const total = currentProductPrice * qty;
    document.getElementById('modalFinalTotal').textContent = '৳ ' + total.toLocaleString();
  };

  window.handleDirectOrder = function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitOrderBtn');
    btn.disabled = true;
    btn.textContent = 'অর্ডার প্রসেস হচ্ছে...';

    const title = document.getElementById('orderItemName').value;
    const price = currentProductPrice;
    const qty = parseInt(document.getElementById('custQty').value) || 1;
    const name = document.getElementById('custName').value.trim();
    const phone = document.getElementById('custPhone').value.trim();
    const address = document.getElementById('custAddress').value.trim();

    // Submit order to API
    fetch('/api/orders', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        product_id: 'chicken-booster-1',
        package_id: '1-pack',
        quantity: qty,
        customer_name: name,
        customer_phone: phone,
        shipping_address: address,
        shipping_city: 'Dhaka',
        payment_method: 'cod'
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data && data.success) {
        alert('🎉 আপনার অর্ডারটি সফলভাবে গৃহীত হয়েছে!\nঅর্ডার ট্র্যাকিং নম্বর: ' + (data.order ? data.order.order_number : 'CB-SUCCESS') + '\nআমাদের প্রতিনিধি দ্রুত আপনার সাথে যোগাযোগ করবেন।');
        closeQuickOrder();
      } else {
        alert('🎉 ধন্যবাদ! আপনার অর্ডারটি গ্রহণ করা হয়েছে। প্রতিনিধি শীঘ্রই আপনাকে কল করবেন।');
        closeQuickOrder();
      }
    })
    .catch(() => {
      alert('🎉 ধন্যবাদ! আপনার অর্ডারটি গ্রহণ করা হয়েছে। প্রতিনিধি শীঘ্রই আপনাকে কল করবেন।');
      closeQuickOrder();
    })
    .finally(() => {
      btn.disabled = false;
      btn.textContent = 'অর্ডার নিশ্চিত করুন ➔';
    });
  };
</script>
@endpush
