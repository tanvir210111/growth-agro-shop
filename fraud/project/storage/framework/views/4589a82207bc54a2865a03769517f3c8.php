
<?php $__env->startSection('title', 'Profile Settings'); ?>
<?php $__env->startSection('contents'); ?>

<style>
    .form-card { background: #fff; border-radius: 1.15rem; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,23,42,.04); margin-bottom: 1.25rem; border: 1px solid #eef2f7; }
    .form-control { border-radius: .75rem; border: 1px solid #e2e8f0; padding: .75rem 1rem; }
    .form-control:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.12); }
    .user-avatar-wrapper { position: relative; width: 70px; height: 70px; }
    .user-avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #ede9fe; background: #eee; }
</style>

<section class="ud-wrap">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <?php echo $__env->make('partial.user.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            <div class="col-lg-9">
                <h4 class="fw-bold text-dark mb-4">প্রোফাইল সেটিংস আপডেট</h4>
                
                <?php if(session('success')): ?> 
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <i class="fa-solid fa-circle-check me-2"></i> <?php echo e(session('success')); ?>

                    </div> 
                <?php endif; ?>

                <form action="<?php echo e(route('user.profile.update')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-card">
                        <h6 class="fw-bold text-primary mb-4"><i class="fa-solid fa-circle-user me-2"></i> ব্যক্তিগত তথ্য ও ছবি</h6>
                        <div class="row g-3">
                            <div class="col-12 mb-3">
                                <label class="form-label d-block fw-bold">প্রোফাইল ছবি পরিবর্তন</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar-wrapper m-0" style="width: 70px; height: 70px;">
                                        <img id="avatar-preview-small" src="<?php echo e(Auth::user()->photo ? asset('assets/images/users/'.Auth::user()->photo) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'); ?>" class="user-avatar">
                                    </div>
                                    <input type="file" name="photo" class="form-control" onchange="previewImage(this)">
                                </div>
                                <small class="text-muted d-block mt-2">অনুমোদিত ফরম্যাট: JPG, PNG (সর্বোচ্চ ২ মেগাবাইট)</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">পুরো নাম</label>
                                <input type="text" name="name" class="form-control" value="<?php echo e(Auth::user()->name); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ফোন নাম্বার</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo e(Auth::user()->phone); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ইমেইল এড্রেস</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e(Auth::user()->email); ?>" required>
                            </div>
							<div class="col-12">
    <label class="form-label">ঠিকানা (Address)</label>
    <textarea name="address" class="form-control" rows="2" placeholder="আপনার পূর্ণ ঠিকানা লিখুন"><?php echo e(Auth::user()->address); ?></textarea>
</div>
                        </div>
                    </div>

                    
                    <div class="form-card border-danger-subtle">
                        <h6 class="fw-bold text-danger mb-4"><i class="fa-solid fa-shield-halved me-2"></i> পাসওয়ার্ড পরিবর্তন</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">নতুন পাসওয়ার্ড</label>
                                <input type="password" name="password" class="form-control" placeholder="পরিবর্তন না করলে ফাঁকা রাখুন">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">কনফার্ম পাসওয়ার্ড</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="পুনরায় নতুন পাসওয়ার্ড লিখুন">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i> আপডেট সেভ করুন
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var small = document.getElementById('avatar-preview-small');
                if (small) small.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.front', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/user/client/profile.blade.php ENDPATH**/ ?>