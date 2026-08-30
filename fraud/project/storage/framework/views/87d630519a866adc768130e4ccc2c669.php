

<?php $__env->startSection('content'); ?>
<style>
    /* 🔹 Dashboard Style */
    .dashboard-header {
        background: linear-gradient(90deg, #6366f1, #a855f7);
        border-radius: 15px;
        color: #fff;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .dashboard-header h2 { font-weight: 700; margin-bottom: 5px; }
    
    .dashboard-card {
        border: none;
        border-radius: 12px;
        color: #fff;
        padding: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 140px;
    }
    .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    .dashboard-card .details h5 { font-size: 16px; opacity: 0.9; margin-bottom: 10px; }
    .dashboard-card .details h2 { font-size: 28px; font-weight: 700; margin: 0; }
    .dashboard-card .icon { font-size: 45px; opacity: 0.3; position: absolute; right: 15px; bottom: 10px; }
    .dashboard-card a { color: rgba(255,255,255,0.8); font-size: 13px; text-decoration: none; margin-top: 10px; display: inline-block; border-bottom: 1px solid; }

    /* 🔹 Custom Gradients */
    .bg-about { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .bg-why { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-pricing { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-brand { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .bg-social { background: linear-gradient(135deg, #ec4899, #db2777); }
    .bg-testimonial { background: linear-gradient(135deg, #64748b, #475569); }
</style>

<div class="content-area">

    
    <div class="dashboard-header">
        <h2><?php echo e(__('Frontend Control Center')); ?></h2>
        <p>Welcome back, <strong><?php echo e(Auth::guard('admin')->user()->name); ?></strong>. Manage your website sections below.</p>
    </div>

    
    <div class="row g-4">

        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card bg-about">
                <div class="details">
                    <h5><?php echo e(__('About Section')); ?></h5>
                    <h2><?php echo e(__('Edit')); ?></h2>
                    <a href="<?php echo e(route('admin.about.edit')); ?>"><?php echo e(__('Update Content')); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="icon"><i class="fas fa-address-card"></i></div>
            </div>
        </div>

        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card bg-why">
                <div class="details">
                    <h5><?php echo e(__('Why Choose Us')); ?></h5>
                    <h2>
                        <?php $why_count = \App\Models\WhyChooseUs::class; ?>
                        <?php echo e(class_exists($why_count) ? $why_count::count() : '0'); ?>

                    </h2>
                    <a href="<?php echo e(route('admin.why-choose-us.index')); ?>"><?php echo e(__('Manage Features')); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="icon"><i class="fas fa-question-circle"></i></div>
            </div>
        </div>

        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card bg-pricing">
                <div class="details">
                    <h5><?php echo e(__('Pricing Plans')); ?></h5>
                    <h2>
                        <?php $pricing_count = \App\Models\Pricing::class; ?>
                        <?php echo e(class_exists($pricing_count) ? $pricing_count::count() : (class_exists(\App\Models\Price::class) ? \App\Models\Price::count() : '0')); ?>

                    </h2>
                    <a href="<?php echo e(route('admin.pricing.index')); ?>"><?php echo e(__('Manage Plans')); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
            </div>
        </div>

        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card bg-brand">
                <div class="details">
                    <h5><?php echo e(__('Brand Logos')); ?></h5>
                    <h2>
                        <?php $brand_count = \App\Models\Brand::class; ?>
                        <?php echo e(class_exists($brand_count) ? $brand_count::count() : '0'); ?>

                    </h2>
                    <a href="<?php echo e(route('admin.brand.index')); ?>"><?php echo e(__('Manage Brands')); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="icon"><i class="fas fa-crown"></i></div>
            </div>
        </div>

        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card bg-social">
                <div class="details">
                    <h5><?php echo e(__('Social Settings')); ?></h5>
                    <h2>
                        <?php $social_count = \App\Models\Sociallink::class; ?>
                        <?php echo e(class_exists($social_count) ? $social_count::count() : '0'); ?>

                    </h2>
                    <a href="<?php echo e(route('social.link.index')); ?>"><?php echo e(__('Update Links')); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="icon"><i class="fas fa-share-alt"></i></div>
            </div>
        </div>

        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="dashboard-card bg-testimonial">
                <div class="details">
                    <h5><?php echo e(__('Testimonials')); ?></h5>
                    <h2>
                        <?php $testi_count = \App\Models\Testimonial::class; ?>
                        <?php echo e(class_exists($testi_count) ? $testi_count::count() : '0'); ?>

                    </h2>
                    <a href="<?php echo e(route('admin.testimonial.index')); ?>"><?php echo e(__('Manage Reviews')); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="icon"><i class="fas fa-quote-left"></i></div>
            </div>
        </div>

    </div>

    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark font-weight-bold"><i class="fas fa-bolt text-warning me-2"></i> <?php echo e(__('Quick Actions')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="<?php echo e(route('admin.service.edit_text')); ?>" class="btn btn-outline-primary btn-block"><?php echo e(__('Edit Section Text')); ?></a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="<?php echo e(route('admin.counter.index')); ?>" class="btn btn-outline-success btn-block"><?php echo e(__('Counter Section')); ?></a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="<?php echo e(route('admin.brand.create')); ?>" class="btn btn-outline-info btn-block"><?php echo e(__('Add New Brand')); ?></a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="<?php echo e(route('admin.why-choose-us.create')); ?>" class="btn btn-outline-secondary btn-block"><?php echo e(__('Add Why Feature')); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>