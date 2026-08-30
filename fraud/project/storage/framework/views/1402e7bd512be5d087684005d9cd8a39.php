<?php $__env->startSection('meta'); ?>
    <title>Fraud Checker - <?php echo e($gs->title); ?></title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contents'); ?>
<section class="page-hero">
    <div class="container">
        <h1>Courier <span>Fraud Checker</span></h1>
        <p>কাস্টমারের ডেলিভারি হিস্ট্রি চেক করুন — Steadfast, Pathao, RedX, Paperfly, Carrybee</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="info-card p-4 p-md-5">
                    <?php if(Session::has('error')): ?>
                        <div class="alert alert-danger"><?php echo e(Session::get('error')); ?></div>
                    <?php endif; ?>
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('user.fraud.check')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <label class="mb-2 fw-bold">মোবাইল নাম্বার</label>
                        <input type="text" name="phone" class="form-control mb-3" value="<?php echo e(old('phone')); ?>" placeholder="017XXXXXXXX" maxlength="11" required>
                        <button type="submit" class="btn-cd btn-cd-primary w-100">Check Now</button>
                    </form>

                    <div class="mt-3 d-flex justify-content-between small">
                        <a href="<?php echo e(route('user.fraud.logs')); ?>" style="color:var(--accent)">আমার হিস্ট্রি</a>
                        <span class="text-muted">
                            <?php if(($user->fraud_check_daily_limit ?? 0) > 0): ?>
                                Today: <?php echo e((int)$user->today_check_count); ?> / <?php echo e($user->fraud_check_daily_limit); ?>

                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <?php if($recent->count()): ?>
                <div class="mt-4">
                    <h5 class="mb-3">Recent Checks</h5>
                    <?php $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="info-card p-3 mb-2 d-flex justify-content-between">
                            <code><?php echo e($row->phone); ?></code>
                            <span>Success <?php echo e($row->success_ratio); ?>% · Cancel <?php echo e($row->cancel_ratio); ?>%</span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\project\resources\views/user/fraud/index.blade.php ENDPATH**/ ?>