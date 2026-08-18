@extends('layouts.app')

@section('content')
<div class="auth-wrapper-modern">
    <div class="login-container">
        <!-- Left Panel -->
        <div class="login-left">
            <!-- Decorative Backgrounds -->
            <div class="left-bg-glow glow-1"></div>
            <div class="left-bg-glow glow-2"></div>
            
            <div class="brand-top" style="display: flex; justify-content: center; width: 100%; margin-bottom: 30px;">
                <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <span style="font-size: 38px; font-weight: 900; color: #ffffff; letter-spacing: 2px; line-height: 1.1;">Colvo</span>
                    <span style="font-size: 16px; font-weight: 700; color: #ff6b00; letter-spacing: 8px; text-transform: uppercase; margin-top: 4px;">Workspace</span>
                </div>
            </div>
            
            <div class="hero-text">
                <h1>Elevate Your<br>Business<br><span style="color: #ff6b00;">Success.</span></h1>
                <p>Log in to access your dashboard, manage operations, and drive your business forward with our integrated tools.</p>
                <div class="tagline">EVERYTHING IN ONE PLACE</div>
            </div>
        </div>
        
        <!-- Right Panel -->
        <div class="login-right">
            <h2>Account Login</h2>
            <p class="subtitle">Please enter your credentials to continue</p>
            
            @if($errors->any())
                <div class="alert-box alert-error">
                    @foreach ($errors->all() as $error)
                        <div><i class='bx bx-error-circle'></i> {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div class="alert-box alert-success">
                    <i class='bx bx-check-circle'></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">WORK EMAIL</label>
                    <div class="input-wrapper">
                        <i class='bx bx-user'></i>
                        <input type="email" name="email" id="email" placeholder="admin@example.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <div class="input-wrapper">
                        <i class='bx bx-lock-alt'></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="forgot-link">
                    <a href="#">FORGOT PASSWORD?</a>
                </div>

                <button type="submit" class="btn-signin">
                    SIGN IN <i class='bx bx-right-arrow-alt'></i>
                </button>
                
                <div class="login-footer">
                    <span class="new-here">New here? <a href="#">Create Account</a></span>
                    <a href="#" class="btn-vendor">VENDOR ACCESS</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        body { 
            margin: 0; 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #ffedd5 0%, #e2e8f0 100%);
        }
        
        .auth-wrapper-modern {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ffedd5 0%, #e2e8f0 100%);
            padding: 20px;
            box-sizing: border-box;
        }
        
        .login-container {
            display: flex;
            width: 900px;
            height: 550px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            z-index: 10000;
        }
        
        .login-left {
            width: 45%;
            background: rgb(25, 15, 15);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }
        
        .left-bg-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
        }
        .glow-1 {
            width: 200px;
            height: 200px;
            background: #ff6b00;
            top: -50px;
            left: -50px;
        }
        .glow-2 {
            width: 150px;
            height: 150px;
            background: #8b5cf6;
            bottom: -50px;
            right: -50px;
        }
        
        .brand-top {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .hero-text {
            position: relative;
            z-index: 2;
        }
        .hero-text h1 {
            font-size: 38px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 25px;
            letter-spacing: -1px;
            margin-top: 0;
        }
        .hero-text p {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 90%;
        }
        .tagline {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 2.5px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .login-right {
            width: 55%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
            background: #fff;
        }
        
        .login-right h2 {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }
        .login-right .subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 40px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.3s;
        }
        .input-wrapper input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            background: #f8fafc;
            outline: none;
            color: #0f172a;
            font-weight: 600;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .input-wrapper input:focus {
            border-color: #ff6b00;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        }
        .input-wrapper input:focus + i, .input-wrapper:focus-within i {
            color: #ff6b00;
        }
        
        .forgot-link {
            text-align: right;
            margin-bottom: 24px;
        }
        .forgot-link a {
            font-size: 10px;
            font-weight: 900;
            color: #ff6b00;
            text-decoration: none;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .forgot-link a:hover {
            text-decoration: underline;
        }
        
        .btn-signin {
            width: 100%;
            background: #ff6b00;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(255, 107, 0, 0.4);
        }
        .btn-signin i {
            font-size: 18px;
            margin-left: 8px;
        }
        .btn-signin:hover {
            background: #e65c00;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(255, 107, 0, 0.5);
        }
        .btn-signin:active {
            transform: translateY(0);
        }
        
        .login-footer {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .new-here {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        .new-here a {
            color: #ff6b00;
            font-weight: 800;
            text-decoration: none;
        }
        .new-here a:hover {
            text-decoration: underline;
        }
        .btn-vendor {
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            letter-spacing: 1px;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }
        .btn-vendor:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        
        .alert-box {
            margin-bottom: 24px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #f0fdf4;
            color: #22c55e;
            border: 1px solid #bbf7d0;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                height: auto;
                width: 90%;
                margin: 20px;
            }
            .login-left {
                width: 100%;
                padding: 40px 30px;
                border-radius: 24px 24px 0 0;
            }
            .login-right {
                width: 100%;
                padding: 40px 30px;
            }
        }
    </style>
</div>
@endsection
