@extends('layouts.front')

@section('contents')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    .reset-section { background-color: #f1f5f9; min-height: 80vh; display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; }
    .reset-box { background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); width: 100%; max-width: 500px; padding: 40px; }
    .modern-input { width: 100%; height: 50px; padding: 10px 20px; border: 2px solid #eee; border-radius: 10px; margin-bottom: 20px; }
    .btn-modern { width: 100%; height: 50px; background: #d61c59; border: none; border-radius: 10px; color: #fff; font-weight: 600; cursor: pointer; }
</style>

<section class="reset-section">
    <div class="reset-box">
        <div class="text-center mb-4">
            <h3 class="fw-bold">পাসওয়ার্ড রিসেট</h3>
            <p class="text-muted small">আপনার একাউন্টের ফোন নাম্বারটি দিন</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        <form action="{{ route('user.forgot.send') }}" method="POST">
            @csrf
            <label class="fw-bold text-muted small mb-2">ফোন নাম্বার</label>
            <input type="text" name="phone" class="modern-input" placeholder="017xxxxxxxx" required>
            
            <button type="submit" class="btn-modern">ওটিপি পাঠান <i class="fas fa-arrow-right ms-2"></i></button>
        </form>
        
        <div class="text-center mt-4">
            <a href="/login" class="text-decoration-none text-muted small">লগিন পেজে ফিরে যান</a>
        </div>
    </div>
</section>
@endsection