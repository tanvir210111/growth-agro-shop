<?php
    $startUrl = auth()->check() ? route('user.fraud.index') : route('login');
    $nav = [
        ['label' => 'হোম', 'href' => route('frontend.index'), 'active' => request()->routeIs('frontend.index')],
        ['label' => 'কুরিয়ার', 'href' => route('frontend.index').'#couriers'],
        ['label' => 'প্রাইসিং', 'href' => url('/priceing')],
        ['label' => 'ফিচার', 'href' => route('frontend.index').'#features'],
        ['label' => 'সাপোর্ট', 'href' => url('/contact')],
    ];
?>
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">
            <a class="brand-logo" href="<?php echo e(route('frontend.index')); ?>">
                <?php if($gs && !empty($gs->logo)): ?>
                    <img src="<?php echo e(asset('assets/images/logo/'.$gs->logo)); ?>" alt="<?php echo e($gs->title); ?>">
                <?php else: ?>
                    <img src="<?php echo e(asset('assets/front/img/bd/logo.png')); ?>" alt="<?php echo e($gs->title ?? 'BD Courier'); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                    <span class="fallback" style="display:none"><i class="fas fa-shield-alt"></i> <?php echo e($gs->title ?? 'BD Courier'); ?></span>
                <?php endif; ?>
            </a>

            <nav class="nav-pill" aria-label="Main">
                <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($item['href']); ?>" class="<?php echo e(!empty($item['active']) ? 'active' : ''); ?>"><?php echo e($item['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <div class="header-actions">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('user.dashboard')); ?>" class="btn btn-ghost btn-sm">ড্যাশবোর্ড</a>
                    <a href="<?php echo e(route('user.fraud.index')); ?>" class="btn btn-primary btn-sm">চেক করুন <i class="fas fa-arrow-right"></i></a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-ghost btn-sm">লগইন</a>
                    <a href="<?php echo e($startUrl); ?>" class="btn btn-primary btn-sm">শুরু করুন <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>

            <button class="mobile-toggle" type="button" id="mobileToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="mobile-menu" id="mobileMenu">
            <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($item['href']); ?>"><?php echo e($item['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="m-actions">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('user.dashboard')); ?>" class="btn btn-outline btn-md">ড্যাশবোর্ড</a>
                    <a href="<?php echo e(route('user.fraud.index')); ?>" class="btn btn-primary btn-md">চেক করুন</a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-outline btn-md">লগইন</a>
                    <a href="<?php echo e($startUrl); ?>" class="btn btn-primary btn-md">ফ্রি অ্যাকাউন্ট খুলুন</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="google_translate_element" style="display:none;"></div>
</header>
<?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/partial/front/header.blade.php ENDPATH**/ ?>