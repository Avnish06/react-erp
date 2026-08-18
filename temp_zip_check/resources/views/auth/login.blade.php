@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <i class='bx bx-hive'></i>
            </div>
            <span class="auth-logo-text">Colovo HRMS</span>
        </div>
        
        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Login to your Executive Service Portal (ESP) account</p>

        @if($errors->any())
            <div class="alert alert-danger">
                <i class='bx bx-error-circle'></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class='bx bx-check-circle'></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" style="margin-top: 30px;">
            @csrf
            
            <div class="form-group" style="text-align: left; margin-bottom: 20px;">
                <label for="email" class="form-label" style="font-weight: 600; color: var(--text-main); font-size: 14px; margin-bottom: 8px; display: block;">Email Address</label>
                <div style="position: relative;">
                    <i class='bx bx-envelope' style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 20px; color: #94a3b8;"></i>
                    <input type="email" name="email" id="email" class="form-input" placeholder="name@company.com" value="{{ old('email') }}" required autofocus 
                           style="width: 100%; padding: 14px 16px 14px 45px; border: 1.5px solid var(--border-color); border-radius: 12px; background: rgba(255, 255, 255, 0.8); font-size: 15px; outline: none; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.01);">
                </div>
            </div>

            <div class="form-group" style="text-align: left; margin-bottom: 30px;">
                <label for="password" class="form-label" style="font-weight: 600; color: var(--text-main); font-size: 14px; margin-bottom: 8px; display: block; display: flex; justify-content: space-between;">
                    Password
                    <a href="#" style="font-size: 13px; color: var(--primary); font-weight: 500; text-decoration: none;">Forgot Password?</a>
                </label>
                <div style="position: relative;">
                    <i class='bx bx-lock-alt' style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 20px; color: #94a3b8;"></i>
                    <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required 
                           style="width: 100%; padding: 14px 16px 14px 45px; border: 1.5px solid var(--border-color); border-radius: 12px; background: rgba(255, 255, 255, 0.8); font-size: 15px; outline: none; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.01);">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" 
                    style="width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--secondary-gradient); color: white; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(255, 107, 0, 0.25);"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(255, 107, 0, 0.35)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(255, 107, 0, 0.25)';">
                Sign In <i class='bx bx-right-arrow-alt' style="vertical-align: middle; font-size: 20px; margin-left: 5px;"></i>
            </button>
        </form>

        <style>
            .form-input:focus {
                border-color: var(--primary) !important;
                box-shadow: 0 0 0 4px var(--primary-glow) !important;
            }
            .auth-card {
                padding: 40px;
                border-radius: 24px;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 214, 184, 0.5);
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05);
            }
            @media (max-width: 480px) {
                .auth-card {
                    padding: 30px 20px;
                }
            }
        </style>


    </div>
</div>
@endsection
