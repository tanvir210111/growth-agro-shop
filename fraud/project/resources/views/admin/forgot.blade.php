<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="GeniusOcean">
    <title>Forgot Password | {{ $gs->title }}</title>

    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/'.$gs->favicon) }}"/>

    <!-- Bootstrap & Fontawesome -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/login/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/login/assets/fonts/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/login/assets/fonts/flaticon/font/flaticon.css') }}">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/login/assets/css/style.css') }}">
</head>

<body id="top">
<div class="page_loader"></div>

<!-- Forgot Password Section Start -->
<div class="login-7">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="form-section">
                    <div class="logo text-center mb-3">
                        <a href="{{ route('admin.login') }}">
                            <img src="{{ asset('assets/images/logo/'.$gs->logo) }}" alt="logo">
                        </a>
                    </div>

                    <h3 class="text-center mb-4">{{ __('Forgot Password') }}</h3>

                    <div class="login-inner-form">
                        @include('includes.admin.form-login')

                        <form id="forgotform" action="{{ route('admin.forgot.submit') }}" method="POST">
                            {{ csrf_field() }}

                            <div class="form-group clearfix">
                                <div class="form-box">
                                    <input 
                                        name="email" 
                                        type="email" 
                                        class="form-control" 
                                        placeholder="{{ __('Type Email Address') }}" 
                                        required>
                                    <i class="flaticon-mail-2"></i>
                                </div>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary btn-lg btn-theme w-100">
                                    {{ __('Send Reset Link') }}
                                </button>
                            </div>

                            <div class="form-group text-center mt-3">
                                <a href="{{ route('admin.login') }}" class="text-decoration-none">
                                    {{ __('Remember Password? Login Now') }}
                                </a>
                            </div>

                            <input id="authdata" type="hidden" value="Checking...">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Forgot Password Section End -->

<!-- JS Files -->
<script src="{{ asset('assets/frontend/login/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/frontend/login/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/frontend/login/assets/js/jquery.validate.min.js') }}"></script>

<!-- ✅ FIXED: AJAX Forgot Password -->
<script>
$(document).ready(function(){
    $('#forgotform').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type=submit]');
        var oldHtml = btn.html();
        var authdata = $('#authdata').val();
        var formData = form.serialize();

        btn.html(authdata).prop('disabled', true);
        $.ajax({
            method: "POST",
            url: form.attr('action'),
            data: formData,
            success: function(data){
                $('.alert-success, .alert-danger').remove();
                if(data.errors){
                    form.before('<div class="alert alert-danger">'+ data.errors +'</div>');
                } else {
                    form.before('<div class="alert alert-success">'+ data +'</div>');
                }
                btn.html(oldHtml).prop('disabled', false);
            },
            error: function(xhr){
                $('.alert-success, .alert-danger').remove();
                form.before('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
                btn.html(oldHtml).prop('disabled', false);
            }
        });
    });
});
</script>

</body>
</html>
