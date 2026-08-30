<?php
    $startUrl = auth()->check() ? route('user.fraud.index') : route('login');
?>

<footer class="site-footer">
    <div class="footer-cta-wrap">
        <div class="container">
            <div class="footer-cta">
                <div>
                    <h3>আজই শুরু করুন</h3>
                    <p>ফ্রি অ্যাকাউন্ট খুলুন এবং রিটার্ন কমাতে শুরু করুন।</p>
                </div>
                <a href="<?php echo e($startUrl); ?>" class="btn btn-white btn-md">ফ্রি শুরু করুন</a>
            </div>
        </div>
    </div>

    <div class="container footer-main">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <?php if(!empty($gs->footer_logo)): ?>
                        <img src="<?php echo e(asset('assets/images/logo/'.$gs->footer_logo)); ?>" alt="<?php echo e($gs->title); ?>" style="filter:none;height:40px;">
                    <?php elseif(!empty($gs->logo)): ?>
                        <img src="<?php echo e(asset('assets/images/logo/'.$gs->logo)); ?>" alt="<?php echo e($gs->title); ?>" style="filter:none;height:40px;">
                    <?php else: ?>
                        <h5 class="text-white mb-3"><?php echo e($gs->title ?? 'BD Courier'); ?></h5>
                    <?php endif; ?>
                    <p><?php echo e($gs->footer_details ?? 'বাংলাদেশের #১ ই-কমার্স কাস্টমার ভেরিফিকেশন প্ল্যাটফর্ম। সকল কুরিয়ার সার্ভিসের ডেলিভারি রিপোর্ট এক জায়গায়।'); ?></p>
                    <div class="social">
                        <?php $__currentLoopData = $social_links->sortBy('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social_link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($social_link->link); ?>" target="_blank" rel="noopener" aria-label="social"><i class="<?php echo e($social_link->icon); ?>"></i></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h5>প্রোডাক্ট</h5>
                <ul>
                    <li class="mb-2"><a href="<?php echo e(route('frontend.index')); ?>#features">ফিচার সমূহ</a></li>
                    <li class="mb-2"><a href="<?php echo e(route('frontend.index')); ?>#couriers">কুরিয়ার ডিরেক্টরি</a></li>
                    <li class="mb-2"><a href="<?php echo e(url('/priceing')); ?>">প্রাইসিং</a></li>
                    <li class="mb-2"><a href="<?php echo e($startUrl); ?>">Fraud Checker</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h5>কোম্পানি</h5>
                <ul>
                    <li class="mb-2"><a href="<?php echo e(url('/about')); ?>">আমাদের সম্পর্কে</a></li>
                    <li class="mb-2"><a href="<?php echo e(url('/contact')); ?>">সাপোর্ট</a></li>
                    <li class="mb-2"><a href="<?php echo e(route('terms.service')); ?>">Terms of Service</a></li>
                    <li class="mb-2"><a href="<?php echo e(route('privacy.policy')); ?>">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <h5>যোগাযোগ</h5>
                <ul>
                    <li class="contact-li">
                        <i class="fab fa-whatsapp wa"></i>
                        <a href="https://wa.me/88<?php echo e(preg_replace('/[^0-9]/', '', $gs->phone ?? '01772411171')); ?>">WhatsApp: <?php echo e($gs->phone); ?></a>
                    </li>
                    <li class="contact-li">
                        <i class="fas fa-phone ph"></i>
                        <a href="tel:+88<?php echo e(preg_replace('/[^0-9]/', '', $gs->phone ?? '')); ?>"><?php echo e($gs->phone); ?></a>
                    </li>
                    <li class="contact-li">
                        <i class="fas fa-map-marker-alt ad"></i>
                        <span><?php echo e($gs->address ?? 'ঢাকা, বাংলাদেশ'); ?></span>
                    </li>
                    <li class="contact-li">
                        <i class="fas fa-envelope" style="color:#c4b5fd"></i>
                        <span><?php echo e($gs->email); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="foot-bot">
            <p class="mb-0">&copy; <?php echo e(date('Y')); ?> <?php echo e($gs->title ?? 'BD Courier'); ?>. All rights reserved.</p>
            <div>
                <a href="<?php echo e(route('privacy.policy')); ?>" class="me-3">Privacy</a>
                <a href="<?php echo e(route('terms.service')); ?>">Terms</a>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\project\resources\views/partial/front/footer.blade.php ENDPATH**/ ?>