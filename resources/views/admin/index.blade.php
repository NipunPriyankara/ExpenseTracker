@extends('layouts.app')

@section('title', 'Admin Panel - User Management')

@section('content')
<!-- Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <span class="brand-badge bg-danger text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </span>
            <span>Admin Panel & User Management</span>
        </h4>
        <p class="text-muted small mb-0">System overview, user accounts, financial statistics, password resets, and user controls.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-person-plus-fill"></i>
            <span>Create User</span>
        </button>
    </div>
</div>

<!-- System Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Total Users -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Registered Users</span>
                <div class="stat-card-icon" style="background-color: #4f46e515; color: #4f46e5;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ number_format($totalUsers) }}</h3>
            <div class="d-flex align-items-center gap-1 small text-muted">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0" style="font-size: 0.68rem;">
                    {{ $totalAdmins }} {{ Str::plural('Admin', $totalAdmins) }}
                </span>
                <span>•</span>
                <span class="badge bg-light text-muted border rounded-pill px-2 py-0" style="font-size: 0.68rem;">
                    {{ $totalUsers - $totalAdmins }} Regular
                </span>
            </div>
        </div>
    </div>

    <!-- Total Incomes -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">System Incomes</span>
                <div class="stat-card-icon" style="background-color: #10b98115; color: #10b981;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-success">Rs. {{ number_format($totalIncomesAmount, 2) }}</h3>
            <div class="small text-muted">
                {{ number_format($totalIncomesCount) }} income transactions
            </div>
        </div>
    </div>

    <!-- Total Expenses -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">System Expenses</span>
                <div class="stat-card-icon" style="background-color: #ef444415; color: #ef4444;">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-danger">Rs. {{ number_format($totalExpensesAmount, 2) }}</h3>
            <div class="small text-muted">
                {{ number_format($totalExpensesCount) }} expense entries
            </div>
        </div>
    </div>

    <!-- Active Loans -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Active Loans</span>
                <div class="stat-card-icon" style="background-color: #0ea5e915; color: #0ea5e9;">
                    <i class="bi bi-bank"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-primary">{{ number_format($totalActiveLoans) }}</h3>
            <div class="small text-muted">
                Active loans & debts tracked
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small fw-semibold text-muted">Search User</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by user name or email address..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label small fw-semibold text-muted">Role</label>
            <select name="role" class="form-select form-select-sm">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrators</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Standard Users</option>
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label small fw-semibold text-muted">Sort By</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest Registered</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest Registered</option>
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A - Z)</option>
                <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z - A)</option>
                <option value="recent_login" {{ request('sort') === 'recent_login' ? 'selected' : '' }}>Recently Active</option>
                <option value="most_logins" {{ request('sort') === 'most_logins' ? 'selected' : '' }}>Most Logins</option>
            </select>
        </div>

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-3" title="Apply Filter">
                <i class="bi bi-funnel"></i>
            </button>
            @if(request()->hasAny(['search', 'role', 'sort']))
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-sm rounded-3 border" title="Reset Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- User Count Summary Ribbon -->
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div class="small text-muted">
        Showing <strong>{{ $users->total() }}</strong> users in system
    </div>
    <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6 fw-bold">
        Total Users: {{ $totalUsers }}
    </div>
</div>

@if($users->count() > 0)
    <!-- Desktop Table View -->
    <div class="card card-custom d-none d-md-block overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th class="ps-3 py-3">USER</th>
                        <th>ROLE</th>
                        <th>JOINED DATE</th>
                        <th>LOGIN ACTIVITY</th>
                        <th class="text-center pe-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <!-- User info -->
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-3">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover shadow-sm" style="width: 38px; height: 38px;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <span>{{ $user->name }}</span>
                                            @if($user->id === Auth::id())
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.65rem;">You</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.78rem;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td>
                                @if($user->isAdmin())
                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1 shadow-sm d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-shield-lock-fill"></i> Admin
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-person me-1"></i> User
                                    </span>
                                @endif
                            </td>

                            <!-- Joined Date -->
                            <td class="text-muted small">
                                <div>{{ $user->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 0.72rem;">{{ $user->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Login Activity -->
                            <td>
                                <div>
                                    @if($user->last_login_at)
                                        <div class="fw-semibold text-dark small d-flex align-items-center gap-1">
                                            <i class="bi bi-clock-history text-primary"></i>
                                            <span>{{ $user->last_login_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            {{ $user->last_login_at->format('M d, Y (h:i A)') }}
                                            @if($user->last_login_ip)
                                                • <span class="font-monospace">{{ $user->last_login_ip }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bi bi-dash-circle me-1"></i> No logins recorded
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <!-- View Details -->
                                    <button type="button" class="btn btn-outline-primary view-user-btn" data-user-id="{{ $user->id }}" title="View User Details & Login History">
                                        <i class="bi bi-eye"></i> Details
                                    </button>

                                    <!-- Reset Password -->
                                    <button type="button" class="btn btn-outline-warning text-dark reset-pw-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" title="Reset Password">
                                        <i class="bi bi-key-fill"></i> Reset PW
                                    </button>

                                    <!-- Role Toggle -->
                                    @if($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.toggle-role', $user->id) }}" class="d-inline" onsubmit="return confirm('Change administrator role for {{ $user->name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary" title="{{ $user->isAdmin() ? 'Demote to regular user' : 'Promote to Administrator' }}">
                                                <i class="bi {{ $user->isAdmin() ? 'bi-shield-slash' : 'bi-shield-check' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Delete User -->
                                        <button type="button" class="btn btn-outline-danger delete-user-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" title="Delete User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="d-md-none mb-4">
        @foreach($users as $user)
            <div class="card card-custom p-3 mb-2">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover shadow-sm" style="width: 38px; height: 38px;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                {{ $user->initials }}
                            </div>
                        @endif
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                <span>{{ $user->name }}</span>
                                @if($user->id === Auth::id())
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.65rem;">You</span>
                                @endif
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div>
                        @if($user->isAdmin())
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 shadow-sm" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill"></i> Admin
                            </span>
                        @else
                            <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.68rem;">User</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-1 mb-3">
                    @if($user->last_login_at)
                        <span class="badge bg-light text-dark border rounded-pill small" style="font-size: 0.68rem;">
                            <i class="bi bi-clock-history text-primary me-1"></i> Last: {{ $user->last_login_at->diffForHumans() }}
                        </span>
                    @else
                        <span class="badge bg-light text-muted border rounded-pill small" style="font-size: 0.68rem;">
                            <i class="bi bi-dash-circle me-1"></i> No logins yet
                        </span>
                    @endif
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small" style="font-size: 0.68rem;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> {{ $user->login_logs_count ?? 0 }} session(s)
                    </span>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-1 border-top gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 small view-user-btn" data-user-id="{{ $user->id }}">
                        <i class="bi bi-eye me-1"></i>Details
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1 small reset-pw-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                        <i class="bi bi-key-fill me-1"></i>Password
                    </button>
                    @if($user->id !== Auth::id())
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 small delete-user-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <i class="bi bi-person-x text-muted display-4 mb-2"></i>
        <h5 class="fw-bold">No Users Found</h5>
        <p class="text-muted small">No users matched your search criteria.</p>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-light border rounded-pill px-4">Reset Filters</a>
        </div>
    </div>
@endif

<!-- ================= MODALS ================= -->

<!-- 1. USER DETAILS MODAL -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-badge bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="userDetailsModalLabel">User Details & Login History</h5>
                        <p class="text-muted small mb-0" id="udUserHeaderEmail">User account overview</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-3">
                <!-- Loader -->
                <div id="udLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted small mt-2">Loading user activity data...</p>
                </div>

                <!-- Content Container -->
                <div id="udContent" class="d-none">
                    <!-- Profile Ribbon -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-3 bg-light rounded-3 border gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div id="udAvatarContainer"></div>
                            <div>
                                <h6 class="fw-bold mb-0" id="udName">User Name</h6>
                                <div class="text-muted small" id="udEmail">email@example.com</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Joined: <span id="udJoinedDate" class="fw-medium text-dark"></span></div>
                            </div>
                        </div>
                        <div id="udRoleBadgeContainer"></div>
                    </div>

                    <!-- Privacy Protected Notice Banner -->
                    <div class="alert alert-light border small text-muted d-flex align-items-center justify-content-between py-2 px-3 rounded-3 mb-3 shadow-none">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-success fs-5"></i>
                            <div>
                                <span class="fw-semibold text-dark">User Privacy Protected:</span>
                                <span class="text-muted">Personal income amounts, expense records, and loan details are strictly confidential and hidden from administrative views.</span>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 flex-shrink-0" style="font-size: 0.7rem;">
                            <i class="bi bi-lock-fill me-1"></i>Private
                        </span>
                    </div>

                    <!-- System Access & Login Activity Stats Grid -->
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-light text-center h-100">
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.72rem;">
                                    <i class="bi bi-box-arrow-in-right text-primary me-1"></i>Total Logins
                                </span>
                                <span class="fw-bold fs-5 text-primary" id="udTotalLogins">0</span>
                                <div class="text-muted" style="font-size: 0.68rem;">Recorded sessions</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-light text-center h-100">
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.72rem;">
                                    <i class="bi bi-clock-history text-info me-1"></i>Last Login
                                </span>
                                <span class="fw-bold fs-6 text-dark d-block text-truncate" id="udLastLoginDiff">Never</span>
                                <div class="text-muted text-truncate" style="font-size: 0.68rem;" id="udLastLoginDate">-</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-light text-center h-100">
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.72rem;">
                                    <i class="bi bi-globe2 text-secondary me-1"></i>Last IP Address
                                </span>
                                <span class="fw-bold fs-6 text-dark d-block text-truncate font-monospace" id="udLastIp">-</span>
                                <div class="text-muted text-truncate" style="font-size: 0.68rem;" id="udLastDevice">-</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-light text-center h-100">
                                <span class="text-muted small fw-semibold d-block" style="font-size: 0.72rem;">
                                    <i class="bi bi-activity text-success me-1"></i>Status
                                </span>
                                <div id="udStatusBadgeContainer" class="mt-1">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">Offline</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Login History Log Table -->
                    <div class="card border rounded-3 overflow-hidden shadow-none">
                        <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-journal-text text-primary"></i>
                                <span class="fw-bold small text-dark">System Login History (Access Log Records)</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.68rem;" id="udLogCountBadge">0 records</span>
                        </div>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.78rem;">
                                <thead class="table-light text-muted sticky-top">
                                    <tr>
                                        <th class="ps-3 py-2">DATE & TIME</th>
                                        <th>DEVICE / OS</th>
                                        <th>BROWSER</th>
                                        <th>IP ADDRESS</th>
                                        <th class="pe-3 text-end">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody id="udLoginLogsTableBody">
                                    <!-- Populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. RESET PASSWORD MODAL -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-badge bg-warning text-dark rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-key-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="resetPasswordModalLabel">Reset User Password</h5>
                        <p class="text-muted small mb-0" id="rpSubTitle">Set a new password for user</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="resetPasswordForm" method="POST" action="">
                @csrf
                <div class="modal-body pt-3">
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        The user will be able to log in with this new password immediately.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">New Password *</label>
                        <div class="input-group">
                            <input type="password" name="password" id="rpNewPassword" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary" id="rpTogglePasswordBtn" title="Show/Hide Password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" id="rpConfirmPassword" class="form-control" placeholder="Repeat the password" required minlength="6">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-none" id="rpGenerateRandomBtn">
                            <i class="bi bi-magic me-1"></i> Generate Strong Password
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-3 px-4 shadow-sm text-dark fw-semibold">
                        <i class="bi bi-check2 me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. CREATE USER MODAL -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-badge bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="createUserModalLabel">Create New User</h5>
                        <p class="text-muted small mb-0">Register a new system user or administrator</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kasun Perera" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. kasun@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="createIsAdmin">
                        <label class="form-check-label small fw-semibold text-dark" for="createIsAdmin">
                            Grant Administrator privileges (Admin Panel access)
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check2 me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. DELETE USER CONFIRMATION MODAL -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill display-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Delete User Account?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete user <strong id="delUserNameText" class="text-dark"></strong> (<span id="delUserEmailText"></span>)?
                </p>

                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 text-start">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i>
                    <strong>Cascade Deletion Notice:</strong> All associated account data (incomes, expenses, loans, budgets, login records, and profile files) will be permanently erased.
                </div>

                <form id="deleteUserForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-3 w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-3 w-50 shadow-sm">
                            <i class="bi bi-trash me-1"></i> Delete User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for Admin Interactive Modals -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------
    // USER DETAILS & LOGIN HISTORY MODAL (AJAX)
    // -------------------------------------------------------------
    const detailsModal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
    const udLoader = document.getElementById('udLoader');
    const udContent = document.getElementById('udContent');
    const udName = document.getElementById('udName');
    const udEmail = document.getElementById('udEmail');
    const udHeaderEmail = document.getElementById('udUserHeaderEmail');
    const udJoinedDate = document.getElementById('udJoinedDate');
    const udAvatarContainer = document.getElementById('udAvatarContainer');
    const udRoleBadgeContainer = document.getElementById('udRoleBadgeContainer');

    const udTotalLogins = document.getElementById('udTotalLogins');
    const udLastLoginDiff = document.getElementById('udLastLoginDiff');
    const udLastLoginDate = document.getElementById('udLastLoginDate');
    const udLastIp = document.getElementById('udLastIp');
    const udLastDevice = document.getElementById('udLastDevice');
    const udStatusBadgeContainer = document.getElementById('udStatusBadgeContainer');
    const udLogCountBadge = document.getElementById('udLogCountBadge');
    const udLoginLogsTableBody = document.getElementById('udLoginLogsTableBody');

    function getDeviceIcon(device, platform) {
        if (device === 'Mobile') {
            return '<i class="bi bi-phone text-primary me-2 fs-6"></i>';
        } else if (device === 'Tablet') {
            return '<i class="bi bi-tablet text-info me-2 fs-6"></i>';
        }
        return '<i class="bi bi-laptop text-secondary me-2 fs-6"></i>';
    }

    function getBrowserIcon(browser) {
        const b = (browser || '').toLowerCase();
        if (b.includes('chrome')) return '<i class="bi bi-browser-chrome text-warning me-1"></i>';
        if (b.includes('edge')) return '<i class="bi bi-browser-edge text-primary me-1"></i>';
        if (b.includes('firefox')) return '<i class="bi bi-browser-firefox text-danger me-1"></i>';
        if (b.includes('safari')) return '<i class="bi bi-compass text-info me-1"></i>';
        return '<i class="bi bi-globe text-muted me-1"></i>';
    }

    document.querySelectorAll('.view-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = btn.getAttribute('data-user-id');
            if (!userId) return;

            udLoader.classList.remove('d-none');
            udContent.classList.add('d-none');
            detailsModal.show();

            fetch("{{ url('/admin/users') }}/" + userId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                udLoader.classList.add('d-none');
                udContent.classList.remove('d-none');

                udName.textContent = data.name;
                udEmail.textContent = data.email;
                udHeaderEmail.textContent = data.email;
                udJoinedDate.textContent = data.joined_date + (data.joined_diff ? ` (${data.joined_diff})` : '');

                // Avatar
                if (data.avatar_url) {
                    udAvatarContainer.innerHTML = `<img src="${data.avatar_url}" alt="${data.name}" class="rounded-circle object-fit-cover shadow-sm" style="width: 48px; height: 48px;">`;
                } else {
                    udAvatarContainer.innerHTML = `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 48px; height: 48px; font-size: 1.1rem;">${data.initials}</div>`;
                }

                // Role badge
                if (data.is_admin) {
                    udRoleBadgeContainer.innerHTML = `<span class="badge bg-danger text-white rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-shield-lock-fill me-1"></i> Administrator</span>`;
                } else {
                    udRoleBadgeContainer.innerHTML = `<span class="badge bg-light text-muted border rounded-pill px-3 py-1"><i class="bi bi-person me-1"></i> Standard User</span>`;
                }

                // Login Stats Overview
                const stats = data.login_stats || {};
                udTotalLogins.textContent = stats.total_logins || 0;
                udLastLoginDiff.textContent = stats.last_login_diff || 'Never';
                udLastLoginDate.textContent = stats.last_login_formatted || '-';
                udLastIp.textContent = stats.last_ip || '-';
                udLastDevice.textContent = (stats.last_device || 'Desktop') + ' • ' + (stats.last_browser || 'Browser');

                // Online/Active Status Badge
                if (stats.is_active_now) {
                    udStatusBadgeContainer.innerHTML = `<span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active Recently</span>`;
                } else {
                    udStatusBadgeContainer.innerHTML = `<span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1"><i class="bi bi-dash-circle me-1"></i> Inactive</span>`;
                }

                // Login logs table rows
                if (data.login_logs && data.login_logs.length > 0) {
                    udLogCountBadge.textContent = data.login_logs.length + ' records';
                    let rowsHtml = '';
                    data.login_logs.forEach(log => {
                        const devIcon = getDeviceIcon(log.device, log.platform);
                        const brIcon = getBrowserIcon(log.browser);
                        rowsHtml += `
                            <tr>
                                <td class="ps-3 py-2">
                                    <div class="fw-semibold text-dark">${log.date_formatted}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">${log.time_formatted} • ${log.diff_for_humans}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${devIcon}
                                        <span>${log.platform || 'OS'} <span class="text-muted small">(${log.device || 'Desktop'})</span></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${brIcon}
                                        <span>${log.browser || 'Browser'}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">${log.ip_address}</span>
                                </td>
                                <td class="pe-3 text-end">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-check2-circle me-1"></i>${log.status || 'success'}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                    udLoginLogsTableBody.innerHTML = rowsHtml;
                } else {
                    udLogCountBadge.textContent = '0 records';
                    udLoginLogsTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-clock-history fs-3 d-block mb-1 text-muted"></i>
                                No login records found for this user.
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(err => {
                udLoader.classList.add('d-none');
                alert('Could not load user login details. Please try again.');
            });
        });
    });

    // -------------------------------------------------------------
    // RESET PASSWORD MODAL
    // -------------------------------------------------------------
    const resetPwModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    const resetPwForm = document.getElementById('resetPasswordForm');
    const rpSubTitle = document.getElementById('rpSubTitle');
    const rpNewPassword = document.getElementById('rpNewPassword');
    const rpConfirmPassword = document.getElementById('rpConfirmPassword');
    const rpTogglePasswordBtn = document.getElementById('rpTogglePasswordBtn');
    const rpGenerateRandomBtn = document.getElementById('rpGenerateRandomBtn');

    document.querySelectorAll('.reset-pw-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = btn.getAttribute('data-user-id');
            const userName = btn.getAttribute('data-user-name');
            const userEmail = btn.getAttribute('data-user-email');

            resetPwForm.action = "{{ url('/admin/users') }}/" + userId + "/reset-password";
            rpSubTitle.textContent = `Set new password for ${userName} (${userEmail})`;
            rpNewPassword.value = '';
            rpConfirmPassword.value = '';
            resetPwModal.show();
        });
    });

    if (rpTogglePasswordBtn) {
        rpTogglePasswordBtn.addEventListener('click', function() {
            const type = rpNewPassword.type === 'password' ? 'text' : 'password';
            rpNewPassword.type = type;
            rpConfirmPassword.type = type;
            rpTogglePasswordBtn.innerHTML = `<i class="bi ${type === 'password' ? 'bi-eye' : 'bi-eye-slash'}"></i>`;
        });
    }

    if (rpGenerateRandomBtn) {
        rpGenerateRandomBtn.addEventListener('click', function() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
            let pwd = '';
            for (let i = 0; i < 10; i++) {
                pwd += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            rpNewPassword.value = pwd;
            rpConfirmPassword.value = pwd;
            rpNewPassword.type = 'text';
            rpConfirmPassword.type = 'text';
            if (rpTogglePasswordBtn) rpTogglePasswordBtn.innerHTML = '<i class="bi bi-eye-slash"></i>';
        });
    }

    // -------------------------------------------------------------
    // DELETE USER MODAL
    // -------------------------------------------------------------
    const deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    const deleteUserForm = document.getElementById('deleteUserForm');
    const delUserNameText = document.getElementById('delUserNameText');
    const delUserEmailText = document.getElementById('delUserEmailText');

    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = btn.getAttribute('data-user-id');
            const userName = btn.getAttribute('data-user-name');
            const userEmail = btn.getAttribute('data-user-email');

            deleteUserForm.action = "{{ url('/admin/users') }}/" + userId;
            delUserNameText.textContent = userName;
            delUserEmailText.textContent = userEmail;

            deleteUserModal.show();
        });
    });
});
</script>
@endsection
