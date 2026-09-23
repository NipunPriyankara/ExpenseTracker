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
                <option value="most_expenses" {{ request('sort') === 'most_expenses' ? 'selected' : '' }}>Most Expenses</option>
                <option value="most_incomes" {{ request('sort') === 'most_incomes' ? 'selected' : '' }}>Most Incomes</option>
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
                        <th>ACTIVITY SUMMARY</th>
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

                            <!-- Activity Summary -->
                            <td>
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" title="Total expenses recorded">
                                        <i class="bi bi-arrow-up-right me-1"></i>{{ $user->expenses_count }} exp (Rs. {{ number_format($user->expenses_sum_amount ?? 0, 2) }})
                                    </span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" title="Total incomes recorded">
                                        <i class="bi bi-arrow-down-left me-1"></i>{{ $user->incomes_count }} inc (Rs. {{ number_format($user->incomes_sum_amount ?? 0, 2) }})
                                    </span>
                                    @if($user->loans_count > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small" title="Active loans">
                                            <i class="bi bi-bank me-1"></i>{{ $user->loans_count }} loans
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <!-- View Details -->
                                    <button type="button" class="btn btn-outline-primary view-user-btn" data-user-id="{{ $user->id }}" title="View User Details & Activity">
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
                                        <button type="button" class="btn btn-outline-danger delete-user-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" data-expenses-count="{{ $user->expenses_count }}" data-incomes-count="{{ $user->incomes_count }}" title="Delete User">
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
                    <span class="badge bg-danger-subtle text-danger border rounded-pill small" style="font-size: 0.68rem;">
                        {{ $user->expenses_count }} exp (Rs. {{ number_format($user->expenses_sum_amount ?? 0, 2) }})
                    </span>
                    <span class="badge bg-success-subtle text-success border rounded-pill small" style="font-size: 0.68rem;">
                        {{ $user->incomes_count }} inc (Rs. {{ number_format($user->incomes_sum_amount ?? 0, 2) }})
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
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 small delete-user-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" data-expenses-count="{{ $user->expenses_count }}" data-incomes-count="{{ $user->incomes_count }}">
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
                        <i class="bi bi-person-badge-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="userDetailsModalLabel">User Details & Activity</h5>
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

                    <!-- Financial Activity Stats Grid -->
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-success-subtle text-center">
                                <span class="text-success small fw-semibold d-block">Total Incomes</span>
                                <span class="fw-bold fs-6 text-success" id="udTotalIncome">Rs. 0.00</span>
                                <div class="text-muted" style="font-size: 0.68rem;" id="udIncomeCount">0 records</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-danger-subtle text-center">
                                <span class="text-danger small fw-semibold d-block">Total Expenses</span>
                                <span class="fw-bold fs-6 text-danger" id="udTotalExpense">Rs. 0.00</span>
                                <div class="text-muted" style="font-size: 0.68rem;" id="udExpenseCount">0 records</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-primary-subtle text-center">
                                <span class="text-primary small fw-semibold d-block">Net Balance</span>
                                <span class="fw-bold fs-6 text-primary" id="udNetBalance">Rs. 0.00</span>
                                <div class="text-muted" style="font-size: 0.68rem;">Earnings - Spends</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 bg-warning-subtle text-center">
                                <span class="text-dark small fw-semibold d-block">Lent / Receivables</span>
                                <span class="fw-bold fs-6 text-dark" id="udLentTotal">Rs. 0.00</span>
                                <div class="text-muted" style="font-size: 0.68rem;" id="udLentCount">0 loans</div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Records Pills -->
                    <div class="d-flex flex-wrap gap-2 mb-3 p-2 bg-light rounded-3 border small">
                        <span class="badge bg-white text-dark border">
                            <i class="bi bi-piggy-bank text-success me-1"></i> <span id="udSavingsCount">0</span> Savings Goals
                        </span>
                        <span class="badge bg-white text-dark border">
                            <i class="bi bi-pie-chart text-warning me-1"></i> <span id="udBudgetsCount">0</span> Monthly Budgets
                        </span>
                        <span class="badge bg-white text-dark border">
                            <i class="bi bi-tags text-primary me-1"></i> <span id="udCustomCatCount">0</span> Custom Categories
                        </span>
                    </div>

                    <!-- Recent Transactions Split -->
                    <div class="row g-3">
                        <!-- Recent Incomes -->
                        <div class="col-12 col-md-6">
                            <span class="fw-bold small text-dark d-block mb-1">
                                <i class="bi bi-wallet2 text-success me-1"></i> Recent Incomes (Latest 5)
                            </span>
                            <div id="udRecentIncomesList" class="d-flex flex-column gap-1"></div>
                        </div>
                        <!-- Recent Expenses -->
                        <div class="col-12 col-md-6">
                            <span class="fw-bold small text-dark d-block mb-1">
                                <i class="bi bi-receipt text-danger me-1"></i> Recent Expenses (Latest 5)
                            </span>
                            <div id="udRecentExpensesList" class="d-flex flex-column gap-1"></div>
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
                    <strong>Cascade Deletion Notice:</strong> All associated financial records (<span id="delExpensesCountText">0</span> expenses, <span id="delIncomesCountText">0</span> incomes, loans, budgets, savings goals, and receipt files) will be permanently erased.
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
    // USER DETAILS MODAL (AJAX)
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

    const udTotalIncome = document.getElementById('udTotalIncome');
    const udIncomeCount = document.getElementById('udIncomeCount');
    const udTotalExpense = document.getElementById('udTotalExpense');
    const udExpenseCount = document.getElementById('udExpenseCount');
    const udNetBalance = document.getElementById('udNetBalance');
    const udLentTotal = document.getElementById('udLentTotal');
    const udLentCount = document.getElementById('udLentCount');

    const udSavingsCount = document.getElementById('udSavingsCount');
    const udBudgetsCount = document.getElementById('udBudgetsCount');
    const udCustomCatCount = document.getElementById('udCustomCatCount');

    const udRecentIncomesList = document.getElementById('udRecentIncomesList');
    const udRecentExpensesList = document.getElementById('udRecentExpensesList');

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
                udJoinedDate.textContent = data.joined_date;

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

                // Financial stats
                udTotalIncome.textContent = 'Rs. ' + data.financial.incomes_total;
                udIncomeCount.textContent = data.financial.incomes_count + ' records';

                udTotalExpense.textContent = 'Rs. ' + data.financial.expenses_total;
                udExpenseCount.textContent = data.financial.expenses_count + ' records';

                udNetBalance.textContent = 'Rs. ' + data.financial.balance;
                udLentTotal.textContent = 'Rs. ' + data.financial.lent_total;
                udLentCount.textContent = data.financial.lent_count + ' loans';

                udSavingsCount.textContent = data.financial.savings_goals_count;
                udBudgetsCount.textContent = data.financial.budgets_count;
                udCustomCatCount.textContent = data.financial.custom_categories_count;

                // Recent Incomes list
                if (data.recent_incomes && data.recent_incomes.length > 0) {
                    let incHtml = '';
                    data.recent_incomes.forEach(i => {
                        incHtml += `
                            <div class="p-2 border rounded-2 bg-light small d-flex justify-content-between align-items-center" style="font-size: 0.77rem;">
                                <div>
                                    <span class="fw-semibold text-dark">${i.category}</span>
                                    <span class="text-muted d-block" style="font-size: 0.7rem;">${i.date} • ${i.note}</span>
                                </div>
                                <span class="fw-bold text-success text-nowrap">+ Rs. ${i.amount}</span>
                            </div>
                        `;
                    });
                    udRecentIncomesList.innerHTML = incHtml;
                } else {
                    udRecentIncomesList.innerHTML = '<div class="text-muted small p-2 bg-light rounded-2 text-center">No incomes recorded yet.</div>';
                }

                // Recent Expenses list
                if (data.recent_expenses && data.recent_expenses.length > 0) {
                    let expHtml = '';
                    data.recent_expenses.forEach(e => {
                        expHtml += `
                            <div class="p-2 border rounded-2 bg-light small d-flex justify-content-between align-items-center" style="font-size: 0.77rem;">
                                <div>
                                    <span class="fw-semibold text-dark">${e.category}</span>
                                    <span class="text-muted d-block" style="font-size: 0.7rem;">${e.date} • ${e.note}</span>
                                </div>
                                <span class="fw-bold text-danger text-nowrap">Rs. ${e.amount}</span>
                            </div>
                        `;
                    });
                    udRecentExpensesList.innerHTML = expHtml;
                } else {
                    udRecentExpensesList.innerHTML = '<div class="text-muted small p-2 bg-light rounded-2 text-center">No expenses recorded yet.</div>';
                }
            })
            .catch(err => {
                udLoader.classList.add('d-none');
                alert('Could not load user details. Please try again.');
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
    const delExpensesCountText = document.getElementById('delExpensesCountText');
    const delIncomesCountText = document.getElementById('delIncomesCountText');

    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = btn.getAttribute('data-user-id');
            const userName = btn.getAttribute('data-user-name');
            const userEmail = btn.getAttribute('data-user-email');
            const expCount = btn.getAttribute('data-expenses-count') || '0';
            const incCount = btn.getAttribute('data-incomes-count') || '0';

            deleteUserForm.action = "{{ url('/admin/users') }}/" + userId;
            delUserNameText.textContent = userName;
            delUserEmailText.textContent = userEmail;
            delExpensesCountText.textContent = expCount;
            delIncomesCountText.textContent = incCount;

            deleteUserModal.show();
        });
    });
});
</script>
@endsection
