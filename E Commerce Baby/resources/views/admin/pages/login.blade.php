<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Baby Fashion BD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body {
            background: linear-gradient(135deg, #1e282c 0%, #222d32 50%, #3c8dbc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: #3c8dbc;
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        .login-header img {
            height: 52px;
            background: #ffffff;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .login-header h3 { font-size: 20px; font-weight: 700; }
        .login-header p { font-size: 13px; opacity: 0.9; margin-top: 4px; }
        .login-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .input-group { position: relative; }
        .input-group i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .form-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }
        .form-input:focus { border-color: #3c8dbc; box-shadow: 0 0 0 3px rgba(60, 141, 188, 0.15); }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #3c8dbc;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: #367fa9; }
        .login-footer {
            text-align: center;
            padding: 15px;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            font-size: 12px;
            color: #6b7280;
        }
        .error-banner {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 18px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <img src="{{ asset('images/logo.png') }}" alt="Baby Fashion BD">
            <h3>Baby Fashion BD</h3>
            <p>Admin Control Panel</p>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="error-banner">
                    <i class="fa fa-exclamation-circle" style="margin-right:4px;"></i> {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-banner">
                    <i class="fa fa-exclamation-circle" style="margin-right:4px;"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="fa fa-envelope"></i>
                        <input type="email" name="email" class="form-input" placeholder="captaincrown@admin.com" value="{{ old('email', 'captaincrown@admin.com') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" value="Aziz625713" required>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-size:13px;">
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; color:#4b5563;">
                        <input type="checkbox" name="remember" value="1" checked> Remember Me
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa fa-sign-in-alt"></i> Sign In to Admin Panel
                </button>
            </form>
        </div>

        <div class="login-footer">
            &copy; {{ date('Y') }} Baby Fashion BD. All rights reserved.
        </div>
    </div>
</body>
</html>
