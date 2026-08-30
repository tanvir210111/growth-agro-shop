
<?php $__env->startSection('content'); ?>
          <div class="content-area">
            <div class="mr-breadcrumb">
              <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading"><?php echo e(__('Website Favicon')); ?></h4>
                    <ul class="links">
                      <li>
                        <a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('Dashboard')); ?> </a>
                      </li>
                      <li>
                        <a href="javascript:;"><?php echo e(__('General Settings')); ?></a>
                      </li>
                      <li>
                        <a href="<?php echo e(route('admin.generalsettings.favicon')); ?>"><?php echo e(__('Website Favicon')); ?></a>
                      </li>
                    </ul>
                </div>
              </div>
            </div>
            <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <div class="add-logo-area">
              <div class="gocover" style="background: url(<?php echo e(asset('assets/images/'.$gs->admin_loader)); ?>) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
              <div class="row justify-content-center">
                <div class="col-lg-6">
                  <form class="uplogo-form" id="geniusform"  action="<?php echo e(route('admin.generalsettings.update')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo e(csrf_field()); ?>

                    <div class="currrent-logo">
                      <h4 class="title">
                        <?php echo e(__('Current Favicon')); ?> :
                      </h4>
                      <img src="<?php echo e($data->favicon ? asset('assets/images/'.$data->favicon):asset('assets/images/noimage.png')); ?>" alt="">
                    </div>
                    <div class="set-logo">
                      <h4 class="title">
                        <?php echo e(__('Set New Favicon')); ?> :
                      </h4>
                      <input class="img-upload1" type="file" name="favicon">
                    </div>
                    <div class="submit-area">
                      <button type="submit" class="submit-btn"><?php echo e(__('Save')); ?></button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\project\resources\views/admin/generalsettings/favicon.blade.php ENDPATH**/ ?>