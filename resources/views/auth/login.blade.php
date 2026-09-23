@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<h5 class="fw-bold text-center mb-3">Sign In to Your Account</h5>

<form method="POST" action="{{ route('login.post') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-envelope text-muted"></i></span>
            <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold mb-0">Password</label>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-lock text-muted"></i></span>
            <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required>
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label small text-muted" for="remember">
            Remember me on this device
        </label>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3 shadow-sm">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>

    <div class="text-center small text-muted">
        Don't have an account yet? 
        <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold">Create account</a>
    </div>
</form>
@endsection
