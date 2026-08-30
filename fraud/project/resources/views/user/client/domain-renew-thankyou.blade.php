@extends('layouts.front')
@section('title', 'ডোমেইন রিনিউ সফল')
@section('contents')
<div class="section-padding py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body text-center py-5 px-4">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-dark mb-3">ডোমেইন রিনিউ</h4>
                        @if(session('success'))
                            <p class="text-success mb-3">{{ session('success') }}</p>
                        @elseif(session('info'))
                            <p class="text-info mb-3">{{ session('info') }}</p>
                        @elseif(session('error'))
                            <p class="text-danger mb-3">{{ session('error') }}</p>
                        @else
                            <p class="text-muted mb-3">মেয়াদ ১ বছর বাড়ানো হয়েছে। আমার ডোমেইন দেখতে লগইন করুন।</p>
                        @endif
                        @auth
                            <a href="{{ route('user.domains.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-globe me-2"></i> আমার ডোমেইন দেখুন
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> লগইন করুন
                            </a>
                            <p class="text-muted small mt-3 mb-0">লগইনের পর অটো আমার ডোমেইন পেজে নিয়ে যাওয়া হবে।</p>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
