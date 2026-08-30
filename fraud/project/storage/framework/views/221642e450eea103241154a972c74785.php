

<?php $__env->startSection('meta'); ?>
      
    <title><?php echo e(__('About Us')); ?> - <?php echo e($gs->title); ?></title>
    <meta name="description" content="আমরা দিচ্ছি প্রিমিয়াম কোয়ালিটির ই-কমার্স ওয়েবসাইট, ডিজিটাল মার্কেটিং এবং সফটওয়্যার সলিউশন। আপনার আইডিয়াকে বাস্তবে রূপ দিতে আমরা প্রস্তুত।">
    <meta name="keywords" content="about us,aboutus,আমাদের সম্পর্কে,Creative design,creativedesign,www.creativedesign.com.bd,creativedesign.com.bd,web designer in bd,web designer in bangladesh,web designer in lalmonir hat,web designer in rangpur,graphics design,graphics designer in bd,graphics designer in lalmonir hat,graphics designer in rangpur,seo expert in bd,seo expert in lalmonir hat,seo expert in rangpur,Laravel expert in bd,Laravel expert in lalmonir hat,Laravel expert in ranpur, news protal in lalmonir hat,Laravel expert in rangpur,news protal,newsprotal,laravel newsprotal in bd,news protal in bd,laravel bangla news protal,laravel ecommerce,laravel ecommerce in bd,ecommece,commerce,bd ecommerce,laravelecommerce,woocommerce,wordpress ecommerce,wordpress wocommerce,ecommerce website,ecommerce website in bd,ecommerce website inb lalmonir hat,wordpress news protal,news theme,laravel teme,laravel news protal theme,theme,bd theme,themebazar,epaper theme,laravel epaper,it solution bd.it solution,itsolutionbd,shadinitsolution,shadhin it solution,sadhinitsolution,www.shadinitsolution.com,shadhinitsolution.com,webdesigner in bhola,software developer in bhola,graphics designer in bhola">
    <meta name="author" content="<?php echo e(__('About Us')); ?> - <?php echo e($gs->title); ?>">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">

    
    <meta property="og:title" content="<?php echo e(__('About Us')); ?> - <?php echo e($gs->title); ?>" />
    <meta property="og:description" content="আমাদের তৈরি করা সেরা ওয়েবসাইট এবং সফটওয়্যার প্রজেক্টগুলো একনজরে দেখে নিন। আপনার আইডিয়াকে বাস্তবে রূপ দিতে আমরা প্রস্তুত।" />
    
    <meta property="og:image" content="https://www.creativedesign.com.bd/assets/images/logo/about.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="<?php echo e(url()->current()); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="<?php echo e(__('About Us')); ?> - <?php echo e($gs->title); ?>" />

    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e(__('About Us')); ?> - <?php echo e($gs->title); ?>">
    <meta name="twitter:description" content="আমরা দিচ্ছি প্রিমিয়াম কোয়ালিটির ই-কমার্স ওয়েবসাইট, ডিজিটাল মার্কেটিং এবং সফটওয়্যার সলিউশন। আপনার আইডিয়াকে বাস্তবে রূপ দিতে আমরা প্রস্তুত।">
    <meta name="twitter:image" content="https://www.creativedesign.com.bd/assets/images/logo/about.jpg">

    
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contents'); ?>

    <?php
        // ডাটাবেস থেকে তথ্য সংগ্রহ
        $about_content = \DB::table('about_section_contents')->first();
        $counters = \DB::table('counters')->get();
        $pricings = \DB::table('pricing_plans')->get();
        $brands = \DB::table('brands')->get(); // ডাটাবেসের সকল ব্র্যান্ড লোগো
    ?>

    <section class="page-hero">
        <div class="container">
            <h1>আমাদের <span>সম্পর্কে</span> জানুন</h1>
            <p>আপনার অনলাইন ব্যবসার নির্ভরযোগ্য ডিজিটাল পার্টনার।</p>
        </div>
    </section>

    <section class="py-5 bg-white overflow-hidden border-bottom">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="about-img-wrapper position-relative">
                        <?php if($about_content && $about_content->image): ?>
                            <img src="<?php echo e(asset('assets/images/about/'.$about_content->image)); ?>" class="img-fluid rounded-4 shadow-lg main-about-img" alt="About Image">
                        <?php endif; ?>
                        
                        <?php $exp = $counters->where('id', 4)->first(); ?>
                        <?php if($exp): ?>
                        <div class="experience-badge bg-white p-3 shadow-lg rounded-3 position-absolute">
                            <h3 class="fw-bold mb-0 text-primary"><?php echo e($exp->count_value); ?></h3>
                            <p class="small mb-0 text-dark"><?php echo e($exp->title); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h6 class="text-primary fw-bold text-uppercase mb-3">কোম্পানি সম্পর্কে —</h6>
                    <h2 class="fw-bold mb-4 display-6" style="color: #1a1a1a;"><?php echo e($about_content->title ?? 'আমাদের রয়েছে অভিজ্ঞতা'); ?></h2>
                    <p class="text-muted fs-5 mb-4 text-justify"><?php echo e($about_content->subtitle); ?></p>

                    <div class="row g-4 mt-2">
                        <?php $__currentLoopData = $counters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $counter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-6">
                            <div class="counter-item d-flex align-items-center p-3 border rounded-3 bg-light">
                                <div class="counter-text">
                                    <h4 class="fw-bold mb-0" style="color: #ff6600;"><?php echo e($counter->count_value); ?></h4>
                                    <p class="small text-muted mb-0"><?php echo e($counter->title); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h6 class="text-primary fw-bold text-uppercase">প্যাকেজ</h6>
                <h2 class="fw-bold">বাজেট অনুযায়ী সেরা প্ল্যান</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <?php $__currentLoopData = $pricings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pricing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card h-100 p-4 border text-center rounded-4 shadow-sm bg-white <?php echo e($pricing->is_featured ? 'featured-pricing' : ''); ?>">
                        <h4 class="fw-bold"><?php echo e($pricing->title); ?></h4>
                        <h2 class="display-5 fw-bold text-primary">৳<?php echo e($pricing->price); ?></h2>
                        <p class="text-muted small"><?php echo e($pricing->duration); ?></p>
                        <hr>
                        <ul class="list-unstyled text-start mb-4" style="min-height: 180px;">
                            <?php $__currentLoopData = explode(',', $pricing->features); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo e(trim($feature)); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <a href="<?php echo e($pricing->order_link); ?>" target="_blank" class="btn <?php echo e($pricing->is_featured ? 'btn-primary' : 'btn-outline-primary'); ?> w-100 rounded-pill fw-bold">অর্ডার করুন</a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>



    <section class="py-5 bg-light">
        <div class="container text-center">
            <div class="custom-package-card p-5 shadow-sm rounded-4 border mx-auto bg-white" style="max-width: 850px; border-radius: 20px;">
                <h2 class="fw-bold mb-3">কাস্টম প্যাকেজ প্রয়োজন?</h2>
                <p class="text-muted mb-4 fs-5">আপনার যদি বিশেষ কোনো চাহিদা থাকে তবে আমাদের সরাসরি কল দিন অথবা মেসেজ করুন।</p>
                <a href="<?php echo e(url('/contact')); ?>" class="btn btn-outline-dark btn-lg px-5 py-2 rounded-pill fw-bold" style="border-width: 1.5px;">যোগাযোগ করুন</a>
            </div>
        </div>
    </section>

    <style>
        .main-about-img { border-bottom: 10px solid #ff6600; }
        .experience-badge { bottom: 25px; right: -15px; border-left: 5px solid #ff6600; min-width: 150px; z-index: 2; }
        .text-primary { color: #ff6600 !important; }
        .btn-primary { background-color: #ff6600 !important; border-color: #ff6600 !important; }
        .btn-outline-primary { color: #ff6600 !important; border-color: #ff6600 !important; transition: 0.3s; }
        .btn-outline-primary:hover { background-color: #ff6600 !important; color: #fff !important; }
        .featured-pricing { border: 2.5px solid #ff6600 !important; transform: scale(1.05); z-index: 1; }
        .brand-img { opacity: 0.7; transition: 0.3s; filter: grayscale(100%); }
        .brand-img:hover { opacity: 1; filter: grayscale(0%); transform: scale(1.1); }
        .text-justify { text-align: justify; }
        /* স্লাইডার অ্যারো ডিজাইন */
        .slick-prev:before, .slick-next:before { color: #ff6600; font-size: 24px; }
    </style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/frontend/about.blade.php ENDPATH**/ ?>