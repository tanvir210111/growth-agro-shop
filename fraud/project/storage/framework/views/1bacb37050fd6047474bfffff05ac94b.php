<?php $__env->startSection('meta'); ?>
    <title><?php echo e($gs->title); ?> — কাস্টমার ভেরিফিকেশন ও রিস্ক চেক</title>
    <meta name="description" content="অর্ডার কনফার্মের আগেই কাস্টমারের ডেলিভারি হিস্ট্রি ও রিস্ক স্কোর চেক করুন। Pathao, SteadFast, RedX সহ ৬+ কুরিয়ার।">
    <meta property="og:title" content="<?php echo e($gs->title); ?> — Fraud Checker" />
    <meta property="og:image" content="<?php echo e(asset('assets/images/logo/'.$gs->og_baner)); ?>" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contents'); ?>
<?php
    $startUrl = auth()->check() ? route('user.fraud.index') : route('login');
    $phone = preg_replace('/[^0-9]/', '', $gs->phone ?? '01772411171');
?>


<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <div class="hero-badge">
                    <i class="fas fa-star"></i>
                    <span>৪০,০০০+ ব্যবসায়ী ব্যবহার করছেন</span>
                </div>

                <h1>
                    অর্ডার কনফার্মের <span class="text-gradient">আগেই</span> জানুন<br>
                    কাস্টমার <span class="text-gradient">বিশ্বস্ত কিনা</span>
                </h1>

                <p class="lead">
                    ফোন নম্বর দিয়ে সার্চ করুন — সাথে সাথে দেখুন কাস্টমারের সকল কুরিয়ার সার্ভিসের ডেলিভারি হিস্ট্রি এবং AI রিস্ক স্কোর।
                </p>

                <div class="hero-stats">
                    <div class="item">
                        <div class="ico"><i class="fas fa-users"></i></div>
                        <div class="txt"><b>৪০,০০০+</b><span>ব্যবহারকারী</span></div>
                    </div>
                    <div class="item">
                        <div class="ico"><i class="fas fa-search"></i></div>
                        <div class="txt"><b>৩ লাখ+</b><span>দৈনিক সার্চ</span></div>
                    </div>
                    <div class="item">
                        <div class="ico"><i class="fas fa-chart-line"></i></div>
                        <div class="txt"><b>৩৫%</b><span>রিটার্ন কম</span></div>
                    </div>
                </div>

                <div class="hero-btns">
                    <a href="<?php echo e($startUrl); ?>" class="btn-hero-primary"><i class="fas fa-search"></i> ফ্রি সার্চ করুন</a>
                    <a href="<?php echo e($startUrl); ?>" class="btn-hero-outline">অ্যাকাউন্ট খুলুন <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="mock-wrap">
                    <div class="mock-card">
                        <div class="mock-search">
                            <i class="fas fa-search"></i>
                            <span>01712-XXXXXX</span>
                        </div>

                        <div class="mock-result">
                            <div class="mock-result-top">
                                <div class="mock-status">
                                    <div class="shield"><i class="fas fa-shield-alt"></i></div>
                                    <div>
                                        <strong>নিরাপদ</strong>
                                        <small>রিস্ক লেভেল</small>
                                    </div>
                                </div>
                                <div class="mock-score">
                                    <strong>৯৪%</strong>
                                    <small>সফল ডেলিভারি</small>
                                </div>
                            </div>

                            <div class="mock-bar"><span></span></div>

                            <div class="mock-stats">
                                <div>
                                    <b>১২৫</b>
                                    <span>মোট অর্ডার</span>
                                </div>
                                <div class="ok">
                                    <b>১১৮</b>
                                    <span>সফল</span>
                                </div>
                                <div class="ret">
                                    <b>৭</b>
                                    <span>রিটার্ন</span>
                                </div>
                            </div>

                            <div class="mock-badge">
                                <i class="fas fa-check-circle"></i>
                                <span>শিপ করুন — বিশ্বস্ত কাস্টমার</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="couriers" id="couriers">
    <div class="container">
        <div class="eyebrow"><span style="width:.5rem;height:.5rem;border-radius:50%;background:#7c3aed;display:inline-block;animation:pulse 1.4s infinite"></span> লাইভ ডেটা</div>
        <h2>যেসব কুরিয়ার সার্ভিসের <span class="text-gradient">ডেটা পাবেন</span></h2>
        <p class="sub">বাংলাদেশের শীর্ষ কুরিয়ার সার্ভিসগুলোর রিয়েল-টাইম ডেলিভারি ডেটা এক প্ল্যাটফর্মে</p>
        <div class="logo-row">
            <?php
                $couriers = [
                    ['file' => 'pathao.png', 'name' => 'Pathao'],
                    ['file' => 'steadfast.png', 'name' => 'SteadFast'],
                    ['file' => 'redx.png', 'name' => 'RedX'],
                    ['file' => 'paperfly.png', 'name' => 'PaperFly'],
                    ['file' => 'parceldex.png', 'name' => 'ParcelDex'],
                    ['file' => 'carrybee.png', 'name' => 'CarryBee'],
                ];
            ?>
            <?php $__currentLoopData = $couriers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $src = asset('assets/front/img/bd/'.$c['file']); ?>
                <?php if(file_exists(base_path('../assets/front/img/bd/'.$c['file'])) && filesize(base_path('../assets/front/img/bd/'.$c['file'])) > 500): ?>
                    <img class="clogo" src="<?php echo e($src); ?>" alt="<?php echo e($c['name']); ?>">
                <?php else: ?>
                    <span class="cname"><?php echo e($c['name']); ?></span>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>


<section class="sec sec-soft">
    <div class="container">
        <div class="head reveal">
            <h2>রিটার্নে প্রতিদিন <span style="color:#ef4444">লাখ টাকা</span> লস করছেন?</h2>
            <p><?php echo e($gs->title ?? 'BD Courier'); ?> দিয়ে অর্ডার কনফার্মের আগেই কাস্টমার যাচাই করুন</p>
        </div>

        <div class="cmp reveal">
            <article class="card-loss">
                <div class="card-top">
                    <div class="ico"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <div class="tag">সমস্যা</div>
                        <h3>প্রতিটি রিটার্নে ক্ষতি</h3>
                    </div>
                </div>
                <div class="loss-row"><span><span class="n">1</span>ডেলিভারি চার্জ লস</span><span class="val">৩,০০০৳</span></div>
                <div class="loss-row"><span><span class="n">2</span>প্যাকেজিং খরচ</span><span class="val">১,০০০৳</span></div>
                <div class="loss-row"><span><span class="n">3</span>বিজ্ঞাপন খরচ</span><span class="val">৭,০০০৳</span></div>
                <div class="loss-total">
                    <div>
                        <small>মাসিক ক্ষতি (৩০টি রিটার্ন/দিন)</small>
                        <b>৳ ৩,৩০,০০০</b>
                    </div>
                    <i class="fas fa-chart-line fa-2x" style="opacity:.5"></i>
                </div>
            </article>

            <article class="card-win">
                <div class="card-top">
                    <div class="ico"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <div class="tag">সমাধান</div>
                        <h3><?php echo e($gs->title ?? 'BD Courier'); ?> দিয়ে বাঁচান</h3>
                    </div>
                </div>
                <div class="win-item">
                    <div class="ico"><i class="fas fa-check"></i></div>
                    <div>
                        <h4>অর্ডার কনফার্মের আগে চেক</h4>
                        <p>কাস্টমার ভেরিফাই করে তারপর অর্ডার প্রসেস করুন</p>
                    </div>
                </div>
                <div class="win-item">
                    <div class="ico"><i class="fas fa-percentage"></i></div>
                    <div>
                        <h4>রিটার্ন ৩৫% পর্যন্ত কমান</h4>
                        <p>সন্দেহজনক অর্ডার আগেই শনাক্ত করুন</p>
                    </div>
                </div>
                <div class="win-item">
                    <div class="ico"><i class="fas fa-user-slash"></i></div>
                    <div>
                        <h4>ফেক অর্ডার এড়িয়ে চলুন</h4>
                        <p>ফ্রড কাস্টমারদের হিস্ট্রি এক ক্লিকে দেখুন</p>
                    </div>
                </div>
                <a href="<?php echo e($startUrl); ?>" class="btn btn-primary btn-md win-cta">শুরু করুন <i class="fas fa-arrow-right"></i></a>
            </article>
        </div>

        <div class="stats4 reveal">
            <div><b>৪০,০০০+</b><span>সক্রিয় ব্যবহারকারী</span></div>
            <div><b>৩ লাখ+</b><span>দৈনিক সার্চ</span></div>
            <div><b>৩৫%</b><span>রিটার্ন কমেছে</span></div>
            <div><b>৬+</b><span>কুরিয়ার সার্ভিস</span></div>
        </div>
    </div>
</section>


<section class="sec" id="features">
    <div class="container">
        <div class="head reveal">
            <span class="pill v"><i class="fas fa-star"></i> ফিচার সমূহ</span>
            <h2>আপনার ব্যবসার জন্য <span class="text-gradient">শক্তিশালী টুলস</span></h2>
            <p>ই-কমার্স রিটার্ন কমাতে এবং কাস্টমার ভেরিফিকেশনের জন্য প্রয়োজনীয় সব ফিচার এক জায়গায়।</p>
        </div>
        <div class="feat-tags reveal">
            <span><i class="fas fa-bolt"></i>দ্রুত সার্চ</span>
            <span><i class="fas fa-gift"></i>ফ্রি প্ল্যান</span>
            <span><i class="fas fa-headset"></i>২৪/৭ সাপোর্ট</span>
            <span><i class="fas fa-users"></i>৪০,০০০+ ইউজার</span>
        </div>
        <div class="grid8 reveal">
            <article class="fcard">
                <div class="ico g1"><i class="fas fa-search"></i></div>
                <h3>স্মার্ট সার্চ</h3>
                <p>কাস্টমারের ফোন নম্বর দিয়ে তাৎক্ষণিক ডেলিভারি হিস্ট্রি দেখুন। সকল কুরিয়ার সার্ভিসের রিপোর্ট এক জায়গায়।</p>
            </article>
            <article class="fcard">
                <div class="ico g2"><i class="fas fa-brain"></i></div>
                <h3>রিস্ক অ্যানালাইসিস</h3>
                <p>AI-পাওয়ার্ড রিস্ক স্কোরিং দিয়ে জানুন কোন কাস্টমার বিশ্বস্ত এবং কোন অর্ডারে সতর্ক থাকা উচিত।</p>
            </article>
            <article class="fcard">
                <div class="ico g3"><i class="fas fa-chart-pie"></i></div>
                <h3>ডিটেইলড অ্যানালিটিক্স</h3>
                <p>অর্ডার রেশিও, সাকসেস রেট, রিটার্ন প্যাটার্ন সহ বিস্তারিত পরিসংখ্যান দেখুন।</p>
            </article>
            <article class="fcard">
                <div class="ico g4"><i class="fas fa-truck"></i></div>
                <h3>৬+ কুরিয়ার সাপোর্ট</h3>
                <p>Pathao, Steadfast, RedX, Paperfly সহ বাংলাদেশের জনপ্রিয় কুরিয়ার সার্ভিসের ডেটা।</p>
            </article>
            <article class="fcard">
                <div class="ico g5"><i class="fas fa-code"></i></div>
                <h3>REST API</h3>
                <p>আপনার ওয়েবসাইট বা অ্যাপে সহজে ইন্টিগ্রেট করুন। বিস্তারিত ডকুমেন্টেশন সহ।</p>
            </article>
            <article class="fcard">
                <div class="ico g6"><i class="fab fa-wordpress"></i></div>
                <h3>ওয়ার্ডপ্রেস প্লাগিন</h3>
                <p>WooCommerce স্টোরে ইন্টিগ্রেট করুন কোন কোডিং ছাড়াই। অর্ডার পেজে অটো রিস্ক চেক।</p>
            </article>
            <article class="fcard">
                <div class="ico g7"><i class="fab fa-chrome"></i></div>
                <h3>Chrome এক্সটেনশন</h3>
                <p>ব্রাউজার থেকেই যেকোনো ওয়েবসাইটে ফোন নম্বর সিলেক্ট করে সাথে সাথে চেক করুন।</p>
            </article>
            <article class="fcard">
                <div class="ico g8"><i class="fas fa-mobile-alt"></i></div>
                <h3>মোবাইল অ্যাপ</h3>
                <p>Android এবং iOS অ্যাপ দিয়ে যেকোনো জায়গা থেকে দ্রুত কাস্টমার চেক করুন।</p>
            </article>
        </div>
    </div>
</section>


<section class="sec sec-soft" id="how" style="background:#f8fafc">
    <div class="container">
        <div class="head reveal">
            <span class="pill g">সহজ প্রক্রিয়া</span>
            <h2>মাত্র <span class="text-gradient">৩ টি ধাপে</span> শুরু করুন</h2>
            <p>কোনো জটিলতা নেই। রেজিস্ট্রেশন করুন এবং সাথে সাথে ব্যবহার শুরু করুন।</p>
        </div>
        <div class="steps reveal">
            <div class="step">
                <div class="icon-wrap">
                    <div class="icon s1"><i class="fas fa-search"></i></div>
                    <span class="num">১</span>
                </div>
                <h3>ফোন নম্বর দিন</h3>
                <p>কাস্টমারের ফোন নম্বর সার্চ বক্সে দিন। এক ক্লিকেই সার্চ শুরু হবে।</p>
            </div>
            <div class="step">
                <div class="icon-wrap">
                    <div class="icon s2"><i class="fas fa-file-alt"></i></div>
                    <span class="num">২</span>
                </div>
                <h3>রিপোর্ট দেখুন</h3>
                <p>সকল কুরিয়ার সার্ভিসের ডেলিভারি হিস্ট্রি, সাকসেস রেট এবং রিটার্ন প্যাটার্ন দেখুন।</p>
            </div>
            <div class="step">
                <div class="icon-wrap">
                    <div class="icon s3"><i class="fas fa-check-circle"></i></div>
                    <span class="num">৩</span>
                </div>
                <h3>সিদ্ধান্ত নিন</h3>
                <p>রিস্ক স্কোর দেখে অর্ডার কনফার্ম করুন অথবা অগ্রিম পেমেন্ট নিন। লাভ বাড়ান।</p>
            </div>
        </div>
        <div class="text-center mt-5 reveal">
            <a href="<?php echo e($startUrl); ?>" class="btn btn-primary btn-lg">ফ্রি অ্যাকাউন্ট খুলুন <i class="fas fa-arrow-right"></i></a>
            <p class="mt-3 text-muted" style="font-size:.9rem">কোনো ক্রেডিট কার্ড প্রয়োজন নেই</p>
        </div>
    </div>
</section>


<section class="sec">
    <div class="container">
        <div class="free-cta reveal">
            <div class="free-inner">
                <div>
                    <h2>আজই শুরু করুন <span class="yl">বিনামূল্যে!</span></h2>
                    <p class="desc">কোনো পেমেন্ট ছাড়াই আপনার ব্যবসা সুরক্ষিত করুন। ফ্রি প্ল্যানে সব ফিচার ট্রাই করুন।</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo e($startUrl); ?>" class="btn btn-white btn-lg"><i class="fas fa-rocket"></i> ফ্রি অ্যাকাউন্ট খুলুন</a>
                        <a href="<?php echo e(url('/priceing')); ?>" class="btn btn-ghost-w btn-lg">সব প্ল্যান দেখুন <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <p class="hint">৩০ সেকেন্ডে রেজিস্ট্রেশন • কোনো ক্রেডিট কার্ড লাগবে না</p>
                </div>
                <div class="free-card">
                    <div class="title">
                        <span>ফ্রি প্ল্যান</span>
                        <span class="price">৳০/মাস</span>
                    </div>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> প্রতিদিন ৫০ টি ফ্রি সার্চ</li>
                        <li><i class="fas fa-check-circle"></i> সকল কুরিয়ার সার্ভিসের ডেটা</li>
                        <li><i class="fas fa-check-circle"></i> AI রিস্ক স্কোর দেখুন</li>
                        <li><i class="fas fa-check-circle"></i> ডেলিভারি হিস্ট্রি চেক</li>
                        <li><i class="fas fa-check-circle"></i> কোনো ক্রেডিট কার্ড লাগবে না</li>
                        <li><i class="fas fa-check-circle"></i> চিরকাল ফ্রি</li>
                    </ul>
                    <div class="note">🎉 ৪০,০০০+ ব্যবসায়ী ইতিমধ্যে ব্যবহার করছেন</div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="sec sec-soft" id="contact">
    <div class="container">
        <div class="head reveal">
            <h2>যোগাযোগ করুন</h2>
            <p>যেকোনো প্রশ্ন বা সাহায্যের জন্য আমাদের সাথে যোগাযোগ করুন</p>
        </div>
        <div class="contact2 reveal">
            <a class="ccard wa" href="https://wa.me/88<?php echo e($phone); ?>" target="_blank" rel="noopener">
                <div class="ico"><i class="fab fa-whatsapp"></i></div>
                <h3>WhatsApp</h3>
                <p class="sub">সবচেয়ে দ্রুত রেসপন্স</p>
                <p class="num"><?php echo e($gs->phone); ?></p>
                <span class="go">মেসেজ করুন <i class="fas fa-arrow-right"></i></span>
            </a>
            <a class="ccard call" href="tel:+88<?php echo e($phone); ?>">
                <div class="ico"><i class="fas fa-phone-alt"></i></div>
                <h3>ফোন</h3>
                <p class="sub">সরাসরি কথা বলুন</p>
                <p class="num"><?php echo e($gs->phone); ?></p>
                <span class="go">কল করুন <i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
        <div class="meta-row reveal">
            <div class="mcard">
                <b><i class="fas fa-clock"></i> সাপোর্ট সময়</b>
                <span>সকাল ১০টা - রাত ১০টা</span>
            </div>
            <div class="mcard">
                <b><i class="fas fa-envelope"></i> ইমেইল</b>
                <span><?php echo e($gs->email); ?></span>
            </div>
            <div class="mcard">
                <b><i class="fas fa-map-marker-alt"></i> অফিস</b>
                <span><?php echo e($gs->address); ?></span>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/frontend/index.blade.php ENDPATH**/ ?>