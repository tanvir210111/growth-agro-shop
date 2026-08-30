@extends('layouts.front')

@section('contents')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    .reset-section { background-color: #f1f5f9; min-height: 80vh; display: flex; align-items: center; justify-content: center; font-family: 'Poppins', sans-serif; }
    .reset-box { background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); width: 100%; max-width: 500px; padding: 40px; }
    .modern-input { width: 100%; height: 50px; padding: 10px 20px; border: 2px solid #eee; border-radius: 10px; margin-bottom: 15px; }
    .btn-modern { width: 100%; height: 50px; background: #4e73df; border: none; border-radius: 10px; color: #fff; font-weight: 600; cursor: pointer; }
</style>

<section class="reset-section">
    <div class="reset-box">
        <div class="text-center mb-4">
            <h3 class="fw-bold">নতুন পাসওয়ার্ড</h3>
            <p class="text-muted small">আপনার একাউন্টের জন্য নতুন পাসওয়ার্ড সেট করুন</p>
        </div>

        <form action="{{ route('user.password.update') }}" method="POST">
            @csrf
            
            <label class="fw-bold text-muted small mb-1">নতুন পাসওয়ার্ড</label>
            <input type="password" name="password" class="modern-input" placeholder="********" required>

            <label class="fw-bold text-muted small mb-1">পাসওয়ার্ড নিশ্চিত করুন</label>
            <input type="password" name="password_confirmation" class="modern-input" placeholder="********" required>
            
            <button type="submit" class="btn-modern">পাসওয়ার্ড পরিবর্তন করুন</button>
        </form>
    </div>
</section>
@endsection