@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person-gear text-primary"></i> Profile & Account Settings
                </h4>
                <p class="text-muted small mb-0">Manage your profile photo, username, email address, and account password.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 mb-4 small shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left Column: User Summary Card -->
            <div class="col-12 col-md-4">
                <div class="card card-custom p-4 text-center border-0 shadow-sm rounded-4 h-100">
                    <div class="position-relative mx-auto mb-3" style="width: 110px; height: 110px;">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 id="avatarPreviewImg"
                                 alt="{{ $user->name }}" 
                                 class="rounded-circle shadow-sm border border-3 border-white object-fit-cover w-100 h-100">
                        @else
                            <div id="avatarFallback" class="rounded-circle shadow-sm border border-3 border-white d-flex align-items-center justify-content-center text-white fw-bold fs-2 w-100 h-100" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                                {{ $user->initials }}
                            </div>
                            <img src="" id="avatarPreviewImg" class="rounded-circle shadow-sm border border-3 border-white object-fit-cover w-100 h-100 d-none" alt="Preview">
                        @endif
                        <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; cursor: pointer;" title="Change Photo">
                            <i class="bi bi-camera-fill small"></i>
                        </label>
                    </div>

                    <h5 class="fw-bold mb-1 text-dark">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>

                    <hr class="my-3 text-muted opacity-25">

                    <div class="text-start small">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted"><i class="bi bi-calendar3 me-1 text-primary"></i> Member Since</span>
                            <span class="fw-semibold text-dark">{{ $stats['member_since'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted"><i class="bi bi-receipt me-1 text-danger"></i> Expenses</span>
                            <span class="badge bg-danger-subtle text-danger rounded-pill fw-bold">{{ $stats['total_expenses'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted"><i class="bi bi-cash-stack me-1 text-success"></i> Incomes</span>
                            <span class="badge bg-success-subtle text-success rounded-pill fw-bold">{{ $stats['total_incomes'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-tags me-1 text-info"></i> Categories</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill fw-bold">{{ $stats['total_categories'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings Forms -->
            <div class="col-12 col-md-8 d-flex flex-column gap-4">
                
                <!-- Card 1: Personal Details & Avatar -->
                <div class="card card-custom p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="bg-primary-subtle text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-person-lines-fill fs-6"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Personal Information</h6>
                            <span class="text-muted small">Update your name, profile picture, and email address.</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Photo selection input (hidden or accessible) -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Profile Photo</label>
                            <div class="input-group">
                                <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg,image/webp">
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="form-text small" style="font-size: 0.73rem;">Accepts JPG, PNG, WEBP (Max: 2MB)</span>
                                @if($user->avatar)
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck">
                                        <label class="form-check-label small text-danger" for="removeAvatarCheck" style="font-size: 0.74rem;">
                                            <i class="bi bi-trash"></i> Remove Current Photo
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Username / Full Name -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Username / Name *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="Enter your name or username">
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required placeholder="name@example.com">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Profile Details
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Card 2: Security & Change Password -->
                <div class="card card-custom p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="bg-warning-subtle text-warning rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-shield-lock-fill fs-6 text-dark"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Security & Password</h6>
                            <span class="text-muted small">Update your password to keep your account safe.</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Current Password *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control" required placeholder="Enter current password">
                                <button class="btn btn-outline-secondary toggle-pwd-btn" type="button" data-target="current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted">New Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="new_password" class="form-control" required minlength="6" placeholder="Min. 6 characters">
                                    <button class="btn btn-outline-secondary toggle-pwd-btn" type="button" data-target="new_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted">Confirm New Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation" id="new_password_confirmation" class="form-control" required minlength="6" placeholder="Repeat new password">
                                    <button class="btn btn-outline-secondary toggle-pwd-btn" type="button" data-target="new_password_confirmation">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-warning text-dark fw-semibold rounded-pill px-4 shadow-sm">
                                <i class="bi bi-shield-check me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live Avatar Preview
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreviewImg = document.getElementById('avatarPreviewImg');
    const avatarFallback = document.getElementById('avatarFallback');

    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    if (avatarPreviewImg) {
                        avatarPreviewImg.src = evt.target.result;
                        avatarPreviewImg.classList.remove('d-none');
                    }
                    if (avatarFallback) {
                        avatarFallback.classList.add('d-none');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Password visibility togglers
    document.querySelectorAll('.toggle-pwd-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) icon.className = 'bi bi-eye-slash';
                } else {
                    input.type = 'password';
                    if (icon) icon.className = 'bi bi-eye';
                }
            }
        });
    });
});
</script>
@endpush
@endsection
