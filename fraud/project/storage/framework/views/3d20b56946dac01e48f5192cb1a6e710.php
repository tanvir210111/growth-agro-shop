<?php $__env->startSection('content'); ?>
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><?php echo e(__('Courier Accounts')); ?></h4>
                <ul class="links">
                    <li><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
                    <li><a href="<?php echo e(route('admin.fraud.index')); ?>">Fraud Checker</a></li>
                    <li><a href="javascript:;">Courier Accounts</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <?php if(Session::has('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo e(Session::get('success')); ?>

                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php echo $__env->make('includes.admin.form-error', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle"></i>
            এখান থেকে Pathao, SteadFast, RedX, PaperFly, CarryBee অ্যাকাউন্টের <strong>User ID</strong> ও <strong>Password</strong> পরিবর্তন করতে পারবেন।
            পাসওয়ার্ড ফিল্ড খালি রাখলে আগের পাসওয়ার্ড অপরিবর্তিত থাকবে।
        </div>

        <form action="<?php echo e(route('admin.fraud.couriers.update')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row">
                <?php $__currentLoopData = $couriers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug => $courier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-6 mb-4">
                        <div class="mr-table allproduct p-4 h-100">
                            <h5 class="mb-3">
                                <i class="<?php echo e($courier['icon']); ?> text-primary mr-1"></i>
                                <?php echo e($courier['label']); ?>

                            </h5>

                            <?php $__currentLoopData = $courier['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold"><?php echo e($field['label']); ?></label>
                                    <?php if($field['type'] === 'password'): ?>
                                        <div class="input-group">
                                            <input
                                                type="password"
                                                name="<?php echo e($field['key']); ?>"
                                                id="<?php echo e($field['key']); ?>"
                                                class="form-control input-field"
                                                value=""
                                                placeholder="নতুন পাসওয়ার্ড (খালি = আগেরটা রাখুন)"
                                                autocomplete="new-password"
                                            >
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="<?php echo e($field['key']); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php if(!empty($values[$field['key']])): ?>
                                            <small class="text-muted">বর্তমান পাসওয়ার্ড সেট আছে (••••••••)</small>
                                        <?php else: ?>
                                            <small class="text-danger">এখনো পাসওয়ার্ড সেট করা নেই</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <input
                                            type="text"
                                            name="<?php echo e($field['key']); ?>"
                                            class="form-control input-field"
                                            value="<?php echo e(old($field['key'], $values[$field['key']] ?? '')); ?>"
                                            placeholder="<?php echo e($field['label']); ?>"
                                            autocomplete="off"
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="text-right mb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save"></i> সব অ্যাকাউন্ট সেভ করুন
                </button>
                <a href="<?php echo e(route('admin.fraud.index')); ?>" class="btn btn-light ml-2">ফিরে যান</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.querySelectorAll('.toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(this.getAttribute('data-target'));
        if (!input) return;
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'fas fa-eye';
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\project\resources\views/admin/fraud/courier-accounts.blade.php ENDPATH**/ ?>