@extends('layouts.front')

@section('meta')
    <title>Privacy Policy - {{ $gs->title }}</title>
    <meta name="description" content="Privacy Policy for {{ $gs->title }}. Learn how we collect, use, and protect your personal information.">
    <meta name="keywords" content="privacy policy, data protection, personal information, privacy">
    <meta name="author" content="{{ $gs->title }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph Tags --}}
    <meta property="og:title" content="Privacy Policy - {{ $gs->title }}" />
    <meta property="og:description" content="Learn how we collect, use, and protect your personal information." />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ $gs->title }}" />

    {{-- Twitter Card Tags --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Privacy Policy - {{ $gs->title }}">
    <meta name="twitter:description" content="Learn how we collect, use, and protect your personal information.">
@endsection

@section('contents')

<section class="page-hero py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container py-5 text-center">
        <h1 class="fw-bold display-4 mb-3">
            <i class="fas fa-shield-alt mr-3"></i>Privacy Policy
        </h1>
        <p class="lead opacity-75">Last updated: {{ now()->format('F d, Y') }}</p>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        
                        {{-- Introduction --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-info-circle mr-2"></i>Introduction
                            </h2>
                            <p class="text-muted">
                                Welcome to <strong>{{ $gs->title }}</strong>. We respect your privacy and are committed to protecting your personal data. 
                                This privacy policy will inform you about how we look after your personal data when you visit our website 
                                and tell you about your privacy rights and how the law protects you.
                            </p>
                        </div>

                        {{-- Information We Collect --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-database mr-2"></i>Information We Collect
                            </h2>
                            <p class="text-muted">We may collect, use, store and transfer different kinds of personal data about you:</p>
                            
                            <ul class="list-unstyled ml-4">
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i><strong>Identity Data:</strong> Name, username, date of birth</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i><strong>Contact Data:</strong> Email address, phone number, billing address</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i><strong>Technical Data:</strong> IP address, browser type, device information</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i><strong>Usage Data:</strong> Information about how you use our website and services</li>
                                <li class="mb-2"><i class="fas fa-check text-success mr-2"></i><strong>Marketing Data:</strong> Your preferences in receiving marketing communications</li>
                            </ul>
                        </div>

                        {{-- How We Use Your Information --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-tasks mr-2"></i>How We Use Your Information
                            </h2>
                            <p class="text-muted">We use your personal data for the following purposes:</p>
                            
                            <div class="bg-light p-4 rounded">
                                <ul class="mb-0">
                                    <li class="mb-2">To register you as a new customer</li>
                                    <li class="mb-2">To process and deliver your orders</li>
                                    <li class="mb-2">To manage payments, fees, and charges</li>
                                    <li class="mb-2">To communicate with you about our services</li>
                                    <li class="mb-2">To provide customer support</li>
                                    <li class="mb-2">To improve our website and services</li>
                                    <li class="mb-0">To comply with legal obligations</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Data Security --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-lock mr-2"></i>Data Security
                            </h2>
                            <p class="text-muted">
                                We have put in place appropriate security measures to prevent your personal data from being accidentally lost, 
                                used, accessed, altered, or disclosed in an unauthorized way. We limit access to your personal data to those 
                                employees, agents, contractors, and other third parties who have a business need to know.
                            </p>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Security Measures:</strong> SSL encryption, secure servers, regular security audits, and access controls.
                            </div>
                        </div>

                        {{-- Cookies --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-cookie-bite mr-2"></i>Cookies
                            </h2>
                            <p class="text-muted">
                                Our website uses cookies to distinguish you from other users. This helps us provide you with a good 
                                experience and allows us to improve our site. You can set your browser to refuse cookies, but this 
                                may affect your ability to use our website.
                            </p>
                        </div>

                        {{-- Third-Party Services --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-share-alt mr-2"></i>Third-Party Services
                            </h2>
                            <p class="text-muted">
                                We may share your data with third-party service providers who perform services on our behalf, such as:
                            </p>
                            <ul class="text-muted">
                                <li>Payment processors (SSLCommerz, bKash, etc.)</li>
                                <li>Hosting providers</li>
                                <li>Email service providers</li>
                                <li>Analytics providers (Google Analytics)</li>
                                <li>Social media platforms (Facebook, for chatbot services)</li>
                            </ul>
                        </div>

                        {{-- Your Rights --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-user-shield mr-2"></i>Your Rights
                            </h2>
                            <p class="text-muted">Under data protection laws, you have the following rights:</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-left-primary">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold">Right to Access</h6>
                                            <p class="small mb-0">Request copies of your personal data</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-left-success">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold">Right to Rectification</h6>
                                            <p class="small mb-0">Request correction of inaccurate data</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-left-warning">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold">Right to Erasure</h6>
                                            <p class="small mb-0">Request deletion of your personal data</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-left-danger">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold">Right to Object</h6>
                                            <p class="small mb-0">Object to processing of your data</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Facebook Chatbot Data --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fab fa-facebook-messenger mr-2"></i>Facebook Chatbot Data
                            </h2>
                            <p class="text-muted">
                                If you interact with our Facebook Chatbot, we may collect:
                            </p>
                            <ul class="text-muted">
                                <li>Your Facebook user ID and name</li>
                                <li>Messages you send to our chatbot</li>
                                <li>Your interactions and preferences</li>
                            </ul>
                            <p class="text-muted">
                                This data is used solely to provide chatbot services and improve user experience. 
                                We do not share this data with third parties except as required by law.
                            </p>
                        </div>

                        {{-- Contact Us --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-envelope mr-2"></i>Contact Us
                            </h2>
                            <p class="text-muted">
                                If you have any questions about this Privacy Policy or wish to exercise your rights, please contact us:
                            </p>
                            
                            <div class="bg-light p-4 rounded">
                                <p class="mb-2"><i class="fas fa-envelope text-primary mr-2"></i><strong>Email:</strong> {{ $gs->email }}</p>
                                <p class="mb-2"><i class="fas fa-phone text-success mr-2"></i><strong>Phone:</strong> {{ $gs->phone }}</p>
                                <p class="mb-0"><i class="fas fa-map-marker-alt text-danger mr-2"></i><strong>Address:</strong> {{ $gs->address }}</p>
                            </div>
                        </div>

                        {{-- Changes to This Policy --}}
                        <div class="mb-5">
                            <h2 class="h4 mb-3 text-primary">
                                <i class="fas fa-edit mr-2"></i>Changes to This Policy
                            </h2>
                            <p class="text-muted">
                                We may update this privacy policy from time to time. We will notify you of any changes by posting 
                                the new privacy policy on this page and updating the "Last updated" date.
                            </p>
                        </div>

                        {{-- Footer Note --}}
                        <div class="alert alert-secondary text-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            By using our website, you consent to our Privacy Policy and agree to its terms.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.border-left-primary { border-left: 4px solid #007bff; }
.border-left-success { border-left: 4px solid #28a745; }
.border-left-warning { border-left: 4px solid #ffc107; }
.border-left-danger { border-left: 4px solid #dc3545; }
</style>

@endsection
