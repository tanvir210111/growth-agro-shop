<?php $__env->startSection('content'); ?>
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">Fraud Check History</h4>
                <ul class="links">
                    <li><a href="<?php echo e(route('admin.fraud.index')); ?>">Fraud Checker</a></li>
                    <li><a href="javascript:;">History</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="mr-table allproduct px-3">
            <?php if(Session::has('success')): ?>
                <div class="alert alert-success"><?php echo e(Session::get('success')); ?></div>
            <?php endif; ?>

            <form method="GET" class="mb-3 row">
                <div class="col-md-4">
                    <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Search phone...">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary">Search</button>
                    <a href="<?php echo e(route('admin.fraud.logs')); ?>" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr style="background:#ff6600;color:#fff;">
                            <th>ID</th>
                            <th>Phone</th>
                            <th>By</th>
                            <th>Total</th>
                            <th>Success %</th>
                            <th>Cancel %</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($row->id); ?></td>
                            <td><code><?php echo e($row->phone); ?></code></td>
                            <td><?php echo e($row->checked_by); ?></td>
                            <td><?php echo e($row->aggregate_total); ?></td>
                            <td><?php echo e($row->success_ratio); ?>%</td>
                            <td><?php echo e($row->cancel_ratio); ?>%</td>
                            <td class="small"><?php echo e($row->created_at); ?></td>
                            <td>
                                <a href="<?php echo e(route('admin.fraud.delete', $row->id)); ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this log?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($logs->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/admin/fraud/logs.blade.php ENDPATH**/ ?>