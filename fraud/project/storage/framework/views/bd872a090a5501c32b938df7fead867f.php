<?php $__env->startSection('meta'); ?>
    <title>Fraud Check History - <?php echo e($gs->title); ?></title>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contents'); ?>
<section class="page-hero">
    <div class="container">
        <h1>Check <span>History</span></h1>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="table-responsive info-card p-3">
            <table class="table table-dark table-borderless mb-0" style="--bs-table-bg:transparent;color:#fff;">
                <thead>
                    <tr>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Success %</th>
                        <th>Cancel %</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><code><?php echo e($row->phone); ?></code></td>
                        <td><?php echo e($row->aggregate_total); ?></td>
                        <td><?php echo e($row->success_ratio); ?>%</td>
                        <td><?php echo e($row->cancel_ratio); ?>%</td>
                        <td class="small"><?php echo e($row->created_at); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">কোনো হিস্ট্রি নেই</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3"><?php echo e($logs->links()); ?></div>
        <a href="<?php echo e(route('user.fraud.index')); ?>" class="btn-cd btn-cd-primary mt-3">নতুন চেক</a>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/user/fraud/logs.blade.php ENDPATH**/ ?>