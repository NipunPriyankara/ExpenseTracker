@extends('layouts.guest')

@section('title', 'Create Account')

@section('content')
<h5 class="fw-bold text-center mb-3">Create Free Account</h5>

<form method="POST" action="{{ route('register.post') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label small fw-semibold">Full Name</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-person text-muted"></i></span>
            <input type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. John Silva" required autofocus>
        </div>
        @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-envelope text-muted"></i></span>
            <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-lock text-muted"></i></span>
            <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" placeholder="At least 6 characters" required>
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-lock-fill text-muted"></i></span>
            <input type="password" class="form-control border-start-0 ps-0" id="password_confirmation" name="password_confirmation" placeholder="Re-type password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3 shadow-sm">
        <i class="bi bi-person-plus me-1"></i> Register & Get Started
    </button>

    <div class="text-center small text-muted">
        Already have an account? 
        <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold">Sign In</a>
    </div>
</form>
@endsection
