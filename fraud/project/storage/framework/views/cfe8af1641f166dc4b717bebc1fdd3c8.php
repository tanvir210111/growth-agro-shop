<?php $__env->startSection('content'); ?>

<div class="content-area">
              <div class="mr-breadcrumb">
                <div class="row">
                  <div class="col-lg-12">
                      <h4 class="heading"><?php echo e(__('Website Contents')); ?></h4>
                    <ul class="links">
                      <li>
                        <a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('Dashboard')); ?> </a>
                      </li>
                      <li>
                        <a href="javascript:;"><?php echo e(__('General Settings')); ?></a>
                      </li>
                      <li>
                        <a href=""><?php echo e(__('Website Contents')); ?></a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="add-product-content">
                <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">
                      <div class="gocover" style="background: url(<?php echo e(asset('assets/images/'.$gs->admin_loader)); ?>) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                      <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin.generalsettings.update')); ?>"  method="POST" enctype="multipart/form-data">
                          <?php echo e(csrf_field()); ?>


                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Website Title')); ?> *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="<?php echo e(__('Write Your Site Title Here')); ?>" name="title" value="<?php echo e($data->title); ?>" required="">
                          </div>
                        </div>
						
						<div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Mobile Number')); ?> *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="<?php echo e(__('Write Your Mobile Number')); ?>" name="phone" value="<?php echo e($data->phone); ?>">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Address')); ?> *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <textarea class="input-field" placeholder="<?php echo e(__('Write Your Address')); ?>" name="address"><?php echo e($data->address); ?></textarea>
                          </div>
                        </div>

						
                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Email')); ?> *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="<?php echo e(__('Write Your Email Address')); ?>" name="email" value="<?php echo e($data->email); ?>" required="">
                          </div>
                        </div>
						
						
						                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Footer Details')); ?> *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <textarea class="input-field" placeholder="<?php echo e(__('Write Your Details')); ?>" name="footer_details"><?php echo e($data->footer_details); ?></textarea>
                          </div>
                        </div>
						
						
						
						
						
						
						
						

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Primary Color')); ?> *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="form-group">
                                <div class="input-group colorpicker-component cp">
                                <input type="text" class="form-control input-field color-field cp" name="theme_color" value="<?php echo e($data->theme_color); ?>"/>
                                  <span class="input-group-addon"><i></i></span>
                                </div>
                              </div>

                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Secondery Color')); ?> *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="form-group">
                                <div class="input-group colorpicker-component cp">
                                  <input type="text" class="form-control input-field color-field cp" name="footer_color" value="<?php echo e($data->footer_color); ?>" />
                                  <span class="input-group-addon"><i></i></span>
                                </div>
                              </div>
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Footer Color')); ?> *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="form-group">
                                <div class="input-group colorpicker-component cp">
                                <input type="text" class="form-control input-field color-field cp" name="copyright_color" value="<?php echo e($data->copyright_color); ?>"/>
                                  <span class="input-group-addon"><i></i></span>
                                </div>
                              </div>

                          </div>
                        </div>
						
						
						
						                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__('Facebook Page URL')); ?> *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="<?php echo e(__('Write Your Page URL')); ?>" name="facebook_page_link" value="<?php echo e($data->facebook_page_link); ?>" required="">
                          </div>
                        </div>
						 
						
						
						<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading"><?php echo e(__('SMS API Key')); ?> *</h4>
            <p class="sub-heading"><?php echo e(__('Get this from bulksmsbd.net')); ?></p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="<?php echo e(__('Enter Your SMS API Key')); ?>" name="sms_api_key" value="<?php echo e($data->sms_api_key); ?>">
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading"><?php echo e(__('SMS Sender ID')); ?> *</h4>
            <p class="sub-heading"><?php echo e(__('Approved Sender ID')); ?></p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="<?php echo e(__('Enter Your SMS Sender ID')); ?>" name="sms_sender_id" value="<?php echo e($data->sms_sender_id); ?>">
    </div>
</div>
						
						
						
						<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading"><?php echo e(__('BDCourier API Key')); ?> *</h4>
            <p class="sub-heading"><?php echo e(__('Get this from BDCourier Panel')); ?></p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="<?php echo e(__('Enter Your BDCourier API Key')); ?>" name="bd_courier_api_key" value="<?php echo e($data->bd_courier_api_key); ?>">
    </div>
</div>
				
<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading"><?php echo e(__('UddoktaPay API URL')); ?> *</h4>
            <p class="sub-heading"><?php echo e(__('Get This from UddoktaPay Panel')); ?></p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="<?php echo e(__('Enter UddoktaPay API URL')); ?>" name="uddoktapay_api_url" value="<?php echo e($data->uddoktapay_api_url); ?>">
    </div>
</div>


<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading"><?php echo e(__('UddoktaPay API Key')); ?> *</h4>
            <p class="sub-heading"><?php echo e(__('Get this from UddoktaPay Panel')); ?></p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="<?php echo e(__('Enter UddoktaPay API Key')); ?>" name="uddoktapay_api_key" value="<?php echo e($data->uddoktapay_api_key); ?>">
    </div>
</div>



					<div class="row justify-content-center">
                              <div class="col-lg-3">
                                <div class="left-area">
                                    <h4 class="heading">
                                        Search Console Verification Code
                                        <p class="sub-heading"><?php echo e((__('In Any Language'))); ?></p>
                                    </h4>
                                  
                                </div>
                              </div>
                              <div class="col-lg-6">
                                  <div class="tawk-area">
                                  <textarea name="search_console" required=""><?php echo e($data->search_console); ?></textarea>
                                  </div>
                              </div>
                            </div>
							
		

                    


                            <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading"><?php echo e(__('TimeZone')); ?> *
                                    </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <?php
                                $timezone_identifiers =
                                    DateTimeZone::listIdentifiers(DateTimeZone::ALL);

                                echo "<select name='time_zone'>";

                                echo "<option disabled selected>
                                        Please Select Timezone
                                      </option>";

                                $n = 419;
                                for($i = 0; $i < $n; $i++) {
                                  if($data->time_zone == $timezone_identifiers[$i]){
                                        $msg = 'selected';
                                    }else{
                                        $msg = '';
                                    }
                                    echo "<option value='" . $timezone_identifiers[$i] ."' ".$msg.">" . $timezone_identifiers[$i] . "</option>";
                                }

                                echo "</select>";
                              ?>
                            </div>
                          </div>





                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">

                            </div>
                          </div>
                          <div class="col-lg-6">
                            <button class="addProductSubmit-btn" type="submit"><?php echo e(__('Save')); ?></button>
                          </div>
                        </div>
                     </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('assets/admin/js/notify.js')); ?>"></script>
<script src="<?php echo e(asset('assets/admin/js/distawk.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\project\resources\views/admin/generalsettings/websiteContent.blade.php ENDPATH**/ ?>