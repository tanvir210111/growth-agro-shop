@extends('layouts.front')

@section('meta')
    <title>Data Deletion Instructions - {{ $gs->title ?? 'App' }}</title>
    <meta name="description" content="How to request deletion of your data from our Facebook-integrated services.">
@endsection

@section('contents')

<section class="page-hero py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container py-5 text-center">
        <h1 class="fw-bold display-5 mb-3">
            <i class="fas fa-trash-alt mr-3"></i>Data Deletion Instructions
        </h1>
        <p class="lead opacity-75">Facebook App Data Deletion Policy</p>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <p class="text-muted mb-4">If you have used our services (including Facebook Page Chatbot / Page Connect) and want to delete your data, please follow the steps below.</p>

                        <h5 class="fw-bold mb-3">How to request data deletion</h5>
                        <ol class="mb-4">
                            <li class="mb-2"><strong>Log in</strong> to your account on our website.</li>
                            <li class="mb-2">Go to <strong>Profile / Account Settings</strong>.</li>
                            <li class="mb-2">Use the <strong>Delete my data</strong> or <strong>Disconnect Facebook Page</strong> option to remove your connected pages and related data.</li>
                            <li class="mb-2">Alternatively, <strong>contact us</strong> via the support/contact page with your registered email or phone. We will process your data deletion request within 30 days.</li>
                        </ol>

                        <h5 class="fw-bold mb-3">What we delete</h5>
                        <ul class="mb-4">
                            <li>Your connected Facebook Page information (Page ID, Page name, access token)</li>
                            <li>Chatbot reply settings and automation rules linked to your account</li>
                            <li>Comment automation data</li>
                            <li>Any stored metadata related to your Facebook integration</li>
                        </ul>

                        <p class="text-muted small mb-0">For more details, see our <a href="{{ route('privacy.policy') }}">Privacy Policy</a>. For support, use the <a href="{{ route('contact.us') }}">Contact</a> page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
