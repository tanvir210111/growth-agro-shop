@extends('layouts.front')

@section('contents')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    .reset-section { background-color: #f1f5f9; min-height: 80vh; display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; }
    .reset-box { background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); width: 100%; max-width: 500px; padding: 40px; }
    .modern-input { width: 100%; height: 50px; padding: 10px 20px; border: 2px solid #eee; border-radius: 10px; margin-bottom: 20px; text-align: center; letter-spacing: 5px; font-size: 20px; font-weight: bold; }
    .btn-modern { width: 100%; height: 50px; background: #28a745; border: none; border-radius: 10px; color: #fff; font-weight: 600; cursor: pointer; }
</style>

<section class="reset-section">
    <div class="reset-box">
        <div class="text-center mb-4">
            <h3 class="fw-bold">ওটিপি যাচাই</h3>
            <p class="text-muted small">আপনার মোবাইলে পাঠানো ৬ সংখ্যার কোডটি দিন</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        <form action="{{ route('user.otp.check') }}" method="POST">
            @csrf
            <label class="fw-bold text-muted small mb-2 d-block text-center">যাচাইকরণ কোড (OTP)</label>
            <input type="text" name="otp" class="modern-input" placeholder="______" maxlength="6" required>
            
            <button type="submit" class="btn-modern">যাচাই করুন</button>
        </form>
    </div>
</section>
@endsection