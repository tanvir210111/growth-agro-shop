
<?php $__env->startSection('content'); ?>

          <div class="content-area">
            <div class="mr-breadcrumb">Photo Card Baner

              <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading"><?php echo e(__('Website Logo')); ?></h4>
                    <ul class="links">
                      <li>
                        <a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('Dashboard')); ?> </a>
                      </li>
                      <li>
                        <a href="javascript:;"><?php echo e(__('General Settings')); ?></a>
                      </li>
                      <li>
                        <a href="<?php echo e(route('admin.generalsettings.logo')); ?>"><?php echo e(__('Website Logo')); ?></a>
                      </li>
                    </ul>

                </div>
              </div>
            </div>
            <div class="add-logo-area">
              <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              <div class="row justify-content-center">
                <div class="col-xl-4 col-md-6">
                    <div class="special-box bg-gray">
                        <div class="heading-area">
                            <h4 class="title">
                              <?php echo e(__('Header Logo')); ?>

                            </h4>
                        </div>
                        <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin.generalsettings.update')); ?>" method="POST" enctype="multipart/form-data">
                          <?php echo e(csrf_field()); ?> 
                          

                          <div class="currrent-logo">
                            <img src="<?php echo e($data->logo ? asset('assets/images/logo/'.$data->logo):asset('assets/images/noimage.png')); ?>" alt="">
                          </div>
                          <div class="set-logo">
                            <input class="img-upload1" type="file" name="logo">
                          </div>

                          <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn"><?php echo e(__('Save')); ?></button>
                          </div>
                        </form>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                  <div class="special-box  bg-gray">
                      <div class="heading-area">
                          <h4 class="title">
                            <?php echo e(__('Footer Logo')); ?>

                          </h4>
                      </div>
                      <div class="gocover" style="background: url(<?php echo e(asset('assets/images/'.$gs->admin_loader)); ?>) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                      <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin.generalsettings.update')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo e(csrf_field()); ?>   

                        <div class="currrent-logo">
                          <img src="<?php echo e($data->logo ? asset('assets/images/logo/'.$data->footer_logo):asset('assets/images/noimage.png')); ?>" alt="">
                        </div>
                        <div class="set-logo">
                          <input class="img-upload1" type="file" name="footer_logo">
                        </div>

                        <div class="submit-area mb-4">
                          <button type="submit" class="submit-btn"><?php echo e(__('Save')); ?></button>
                        </div>
                      </form>
                  </div>
              </div>
			  
			  
			  
			  
			  
			  			  	
			  
			  
			  
			  
			  
			  	

			  

			  
			  
			  
			  
			  			  <div class="col-xl-4 col-md-6">
                  <div class="special-box  bg-gray">
                      <div class="heading-area">
                          <h4 class="title">
                            <?php echo e(__('OG Baner')); ?>

                          </h4>
                      </div>
                      <div class="gocover" style="background: url(<?php echo e(asset('assets/images/'.$gs->admin_loader)); ?>) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                      <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin.generalsettings.update')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo e(csrf_field()); ?>   

                        <div class="currrent-logo">
                          <img src="<?php echo e($data->logo ? asset('assets/images/logo/'.$data->og_baner):asset('assets/images/noimage.png')); ?>" alt="">
                        </div>
                        <div class="set-logo">
                          <input class="img-upload1" type="file" name="og_baner">
                        </div>

                        <div class="submit-area mb-4">
                          <button type="submit" class="submit-btn"><?php echo e(__('Save')); ?></button>
                        </div>
                      </form>
                  </div>
              </div>
			  
			  
              </div>
            </div>
          </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\project\resources\views/admin/generalsettings/logo.blade.php ENDPATH**/ ?>