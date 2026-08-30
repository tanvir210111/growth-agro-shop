<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="EliteDesign">

    <title>Login Admin Panel | <?php echo e($gs->title); ?></title>

    <!-- favicon -->
    <link rel="shortcut icon" href="<?php echo e(asset('assets/images/'.$gs->favicon)); ?>" type="image/x-icon">

    <!-- External CSS -->
    <link type="text/css" rel="stylesheet" href="<?php echo e(asset('assets/frontend/login/assets/css/bootstrap.min.css')); ?>">
    <link type="text/css" rel="stylesheet" href="<?php echo e(asset('assets/frontend/login/assets/fonts/font-awesome/css/font-awesome.min.css')); ?>">
    <link type="text/css" rel="stylesheet" href="<?php echo e(asset('assets/frontend/login/assets/fonts/flaticon/font/flaticon.css')); ?>">

    <!-- Google fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Template main CSS -->
    <link type="text/css" rel="stylesheet" href="<?php echo e(asset('assets/frontend/login/assets/css/style.css')); ?>">
</head>

<body id="top">
    <div class="page_loader"></div>

    <!-- Login start -->
    <div class="login-7">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="form-section">
                        <div class="logo text-center mb-3">
                            <a href="#">
                                <img src="<?php echo e(asset('assets/images/logo/'.$gs->logo)); ?>" alt="logo">
                            </a>
                        </div>
                        <h3>আপনাকে <?php echo e($gs->title); ?> এর এডমিন প্যানেলে স্বাগতম!</h3>

                        <div class="login-inner-form mt-4">
                            
                            <?php echo $__env->make('includes.admin.form-login', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                            <form id="loginform" action="<?php echo e(route('admin.login')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="form-group clearfix">
                                    <div class="form-box">
                                        <input name="email" type="email" class="form-control" placeholder="<?php echo e(__('Type Email Address')); ?>" required>
                                        <i class="flaticon-mail-2"></i>
                                    </div>
                                </div>

                                <div class="form-group clearfix">
                                    <div class="form-box">
                                        <input name="password" type="password" class="form-control" placeholder="<?php echo e(__('Type Password')); ?>" required>
                                        <i class="flaticon-password"></i>
                                    </div>
                                </div>

                                <div class="checkbox form-group clearfix">
                                    <div class="form-check float-start">
                                        <input type="checkbox" name="remember" id="rp" class="form-check-input" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="rp"><?php echo e(__('Remember Password')); ?></label>
                                    </div>
                                    <a href="<?php echo e(route('admin.forgot')); ?>" class="link-light float-end forgot-password"><?php echo e(__('Forgot Password?')); ?></a>
                                </div>

                                <div class="form-group clearfix">
                                    <input id="authdata" type="hidden" value="<?php echo e(__('Authenticating...')); ?>">
                                    <button type="submit" class="btn btn-primary btn-lg btn-theme w-100"><?php echo e(__('Login')); ?></button>
                                </div>
                            </form>
                        </div> <!-- /.login-inner-form -->
                    </div> <!-- /.form-section -->
                </div>
            </div>
        </div>
    </div>
    <!-- Login end -->

    <!-- JS Files -->
    <script src="<?php echo e(asset('assets/frontend/login/assets/js/jquery-3.6.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/frontend/login/assets/js/bootstrap.bundle.min.js')); ?>"></script>

    <!-- এখানে আগের AJAX হ্যান্ডলার যুক্ত -->
    <script src="<?php echo e(asset('assets/admin/js/custom.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/admin/js/myscript.js')); ?>"></script>

    <!-- যদি এগুলো না থাকে, নিচের fallback AJAX কোড ব্যবহার হবে -->
    <script>
        if (typeof $.ajax === 'function' && !window.loginHandlerLoaded) {
            window.loginHandlerLoaded = true;
            $('#loginform').on('submit', function(e) {
                e.preventDefault();
                let $form = $(this);
                let url = $form.attr('action');
                let data = $form.serialize();
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    success: function(resp) {
                        if (resp.redirect_url) {
                            window.location.href = resp.redirect_url;
                        } else {
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        if (res && res.errors) {
                            alert(res.errors.join("\n"));
                        } else {
                            alert('Login failed. Please check credentials.');
                        }
                    }
                });
            });
        }
    </script>
</body>
</html>
<?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/admin/login.blade.php ENDPATH**/ ?>