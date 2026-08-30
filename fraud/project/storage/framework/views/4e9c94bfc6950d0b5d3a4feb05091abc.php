
<?php $__env->startSection('meta'); ?>
    <title><?php echo e(__('Login')); ?> - <?php echo e($gs->title); ?></title>
    <meta property="og:title" content="<?php echo e(__('Login')); ?>" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('contents'); ?>


<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .modern-login-section {
        background-color: #f1f5f9;
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 15px;
        font-family: 'Poppins', sans-serif;
    }

    .login-box-wrapper {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden;
        width: 100%;
        max-width: 950px;
        display: flex;
        flex-wrap: wrap;
    }

    /* বাম পাশ (ছবি) */
    .login-image-side {
        width: 50%;
        background: url('<?php echo e(asset('assets/images/login.png')); ?>') no-repeat center center;
        background-size: cover;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 40px;
        color: #fff;
        text-align: center;
    }

    .login-image-side::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        z-index: 1;
    }

    .login-image-side h2, .login-image-side p {
        position: relative; z-index: 2;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* ডান পাশ (ফর্ম) */
    .login-form-side {
        width: 50%;
        padding: 60px 50px;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .modern-input-group { margin-bottom: 20px; position: relative; }
    .modern-input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px; }
    
    .modern-input {
        width: 100%; height: 50px; padding: 10px 20px;
        border: 2px solid #eee; border-radius: 10px;
        font-size: 15px; transition: 0.3s; background: #fdfdfd;
    }
    .modern-input:focus {
        border-color: #ff6b00;
        background: #fff; outline: none;
    }

    .btn-modern {
        width: 100%; height: 50px;
        background: #ff6b00;
        border: none; border-radius: 10px;
        color: #fff; font-weight: 600; font-size: 16px;
        cursor: pointer; transition: 0.3s;
    }
    .btn-modern:hover { background: #b01246; transform: translateY(-2px); }

    .forgot-link {
        text-align: right; display: block; margin-top: -10px; margin-bottom: 20px;
        font-size: 13px; color: #666; text-decoration: none;
    }
    .register-area {
        text-align: center; margin-top: 20px; padding-top: 20px;
        border-top: 1px dashed #ddd;
    }
    .register-trigger {
        color: #6c3483; font-weight: 700; text-decoration: none; cursor: pointer;
    }

    @media (max-width: 768px) {
        .login-image-side { display: none; }
        .login-form-side { width: 100%; padding: 40px 20px; }
    }
</style>

<section class="modern-login-section">
    <div class="login-box-wrapper">
        
        
        <div class="login-image-side">
            <h2 class="fw-bold fs-2 mb-2">Welcome Back!</h2>
            <p class="mb-0 text-white-50"><span style="color: white;">আপনার অ্যাকাউন্টে লগিন করে নিরাপদভাবে সাপোর্ট গ্রহন করুন।</span></p>
        </div>

        
        <div class="login-form-side">
            <div class="mb-4">
                <h3 class="fw-bold text-dark">কাস্টমার লগিন 👋</h3>
                <p class="text-muted small">আপনার ইমেইল অথবা ফোন নাম্বার এবং পাসওয়ার্ড দিন</p>
            </div>

            
            <?php if(Session::has('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
                <?php echo e(Session::get('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
            <div class="alert alert-danger small mb-4">
                <ul class="mb-0 ps-3">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('front.login')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="modern-input-group">
                    
                    <label>ইমেইল অথবা মোবাইল নাম্বার</label>
                    
                    
                    <input type="text" name="email" class="modern-input" placeholder="017xxxxxxxx বা example@gmail.com" required>
                </div>

                <div class="modern-input-group">
                    <label>পাসওয়ার্ড</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="loginPass" class="modern-input" placeholder="********" required>
                        <span onclick="togglePass('loginPass')" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <a href="<?php echo e(route('user.forgot.password')); ?>" class="forgot-link">পাসওয়ার্ড ভুলে গেছেন?</a>

                <button type="submit" class="btn-modern">লগিন করুন</button>
            </form>

            <div class="register-area">
                <p class="text-muted small mb-1">একাউন্ট না থাকলে?</p>
                <a class="register-trigger" data-bs-toggle="modal" data-bs-target="#registerModal">
                    <i class="fas fa-user-plus me-1"></i> রেজিস্ট্রেশন করুন
                </a>
            </div>
        </div>

    </div>
</section>



<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="registerModalLabel">নতুন একাউন্ট খুলুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <form action="<?php echo e(route('front.register')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    
                    
                    <div class="modern-input-group mb-3">
                        <label class="small">আপনার নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="modern-input" placeholder="পুরো নাম" required>
                    </div>

                    
                    <div class="modern-input-group mb-3">
                        <label class="small">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="modern-input" placeholder="017xxxxxxxx" required>
                    </div>

                    
                    <div class="modern-input-group mb-3">
                        <label class="small">ইমেইল <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="modern-input" placeholder="example@gmail.com" required>
                    </div>

                    
                    <div class="modern-input-group mb-3">
                        <label class="small">পাসওয়ার্ড <span class="text-danger">*</span></label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="regPass" class="modern-input" placeholder="********" required>
                            <span onclick="togglePass('regPass')" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #999;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    
                    <div class="modern-input-group mb-4">
                        <label class="small">পাসওয়ার্ড কনফার্ম করুন <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="modern-input" placeholder="********" required>
                    </div>

                    <button type="submit" class="btn-modern" style="background: #ff6b00;">রেজিস্ট্রেশন সম্পন্ন করুন</button>
                </form>

            </div>
            <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
                <span class="small text-muted">ইতিমধ্যে একাউন্ট আছে? <a href="#" data-bs-dismiss="modal" class="text-primary fw-bold text-decoration-none">লগিন করুন</a></span>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePass(id) {
        var x = document.getElementById(id);
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/frontend/log-reg.blade.php ENDPATH**/ ?>