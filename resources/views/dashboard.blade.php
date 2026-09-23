@extends('layouts.app')

@section('title', 'Financial Dashboard')

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('profile.show') }}" class="text-decoration-none" title="Profile Settings">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle object-fit-cover shadow-sm border border-2 border-primary" style="width: 48px; height: 48px;">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 48px; height: 48px;">
                        {{ Auth::user()->initials }}
                    </div>
                @endif
            </a>
            <div>
                <h4 class="fw-bold mb-0">Hello, {{ Auth::user()->name }} 👋</h4>
                <p class="text-muted small mb-0">Here is your financial summary for <strong>{{ date('F Y') }}</strong></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex flex-wrap align-items-center justify-content-md-end gap-2">
        <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 border">
            <i class="bi bi-calendar-event text-primary"></i>
            <span class="small fw-semibold text-muted">Month:</span>
            <input type="month" class="form-control form-control-sm border-0 fw-bold p-0" style="width: auto;" value="{{ $selectedMonth }}" onchange="window.location.href='?month=' + this.value;">
        </div>
        <button class="btn btn-success btn-sm text-white rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#globalAddIncomeModal">
            <i class="bi bi-plus-lg me-1"></i> Add Income
        </button>
        <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickAddModal">
            <i class="bi bi-plus-lg me-1"></i> Add Expense
        </button>
    </div>
</div>

<!-- Main Top Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Income -->
    <div class="col-6 col-lg-3">
        <a href="{{ route('incomes.index') }}" class="text-decoration-none">
            <div class="card card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Income</span>
                    <div class="stat-card-icon bg-success-subtle text-success">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-success">Rs. {{ number_format($currentMonthIncome, 2) }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">This month &bull; View all &rarr;</small>
            </div>
        </a>
    </div>

    <!-- Expenses -->
    <div class="col-6 col-lg-3">
        <a href="{{ route('expenses.index') }}" class="text-decoration-none">
            <div class="card card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Expenses</span>
                    <div class="stat-card-icon bg-danger-subtle text-danger">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-danger">Rs. {{ number_format($currentMonthExpense, 2) }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">This month &bull; View all &rarr;</small>
            </div>
        </a>
    </div>

    <!-- Balance -->
    <div class="col-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-semibold">Net Balance</span>
                <div class="stat-card-icon bg-primary-subtle text-primary">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <h5 class="fw-bold mb-0 {{ $currentMonthBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                Rs. {{ number_format($currentMonthBalance, 2) }}
            </h5>
            <small class="text-muted" style="font-size: 0.72rem;">Income - Expense</small>
        </div>
    </div>

    <!-- Monthly Budget -->
    <div class="col-6 col-lg-3">
        <a href="{{ route('budgets.index') }}" class="text-decoration-none">
            <div class="card card-custom p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Budget Left</span>
                    <div class="stat-card-icon bg-warning-subtle text-warning">
                        <i class="bi bi-piggy-bank"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark">
                    Rs. {{ number_format($remainingBudget, 2) }}
                </h5>
                <small class="text-muted" style="font-size: 0.72rem;">
                    {{ $monthlyBudget > 0 ? $budgetUsagePercent . '% used' : 'Set monthly budget &rarr;' }}
                </small>
            </div>
        </a>
    </div>
</div>

<!-- Balance Sheet / Loans Snapshot (GAAP Standard) -->
<div class="row g-3 mb-4">
    <!-- Receivables (Lent to Others) -->
    <div class="col-12 col-md-6">
        <a href="{{ route('loans.index', ['tab' => 'lent']) }}" class="text-decoration-none">
            <div class="card card-custom p-3 h-100 border-start border-4" style="border-left-color: #0284c7 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="small text-muted fw-semibold d-block">Owed to You (Receivables)</span>
                        <small class="text-info-emphasis" style="font-size: 0.72rem;"><i class="bi bi-shield-check me-1"></i>Balance Sheet Asset (Lent to others)</small>
                    </div>
                    <div class="stat-card-icon bg-info-subtle text-info">
                        <i class="bi bi-arrow-down-left-circle-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between mt-1">
                    <h5 class="fw-bold mb-0 text-dark">Rs. {{ number_format($totalLentReceivable, 2) }}</h5>
                    <small class="text-primary fw-medium" style="font-size: 0.75rem;">Manage Loans &rarr;</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Payables (Debts You Owe) -->
    <div class="col-12 col-md-6">
        <a href="{{ route('loans.index', ['tab' => 'borrowed']) }}" class="text-decoration-none">
            <div class="card card-custom p-3 h-100 border-start border-4" style="border-left-color: #f59e0b !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <span class="small text-muted fw-semibold d-block">Debts You Owe (Payables)</span>
                        <small class="text-warning-emphasis" style="font-size: 0.72rem;"><i class="bi bi-bank me-1"></i>Balance Sheet Liability (Borrowed)</small>
                    </div>
                    <div class="stat-card-icon bg-warning-subtle text-warning">
                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between mt-1">
                    <h5 class="fw-bold mb-0 text-dark">Rs. {{ number_format($totalDebtPayable, 2) }}</h5>
                    <small class="text-primary fw-medium" style="font-size: 0.75rem;">Manage Debts &rarr;</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Secondary Stats Row (Today & Week) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Today's Total Spent</span>
                    <h5 class="fw-bold mb-0 mt-1 text-danger">Rs. {{ number_format($todayExpense, 2) }}</h5>
                </div>
                <div class="badge bg-light text-dark p-2 rounded-3 border">
                    <i class="bi bi-calendar-check me-1 text-primary"></i> Today
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">This Week's Spending</span>
                    <h5 class="fw-bold mb-0 mt-1 text-danger">Rs. {{ number_format($thisWeekExpense, 2) }}</h5>
                </div>
                <div class="badge bg-light text-dark p-2 rounded-3 border">
                    <i class="bi bi-graph-up-arrow me-1 text-info"></i> Current Week
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Budget Progress Bar -->
<div class="card card-custom p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <a href="{{ route('budgets.index') }}" class="fw-semibold small text-decoration-none text-dark">
            <i class="bi bi-speedometer2 me-1 text-primary"></i> Monthly Budget Utilization
        </a>
        <span class="small fw-bold {{ $budgetUsagePercent > 100 ? 'text-danger' : 'text-primary' }}">{{ $budgetUsagePercent }}%</span>
    </div>
    <div class="progress" style="height: 10px; border-radius: 6px;">
        <div class="progress-bar {{ $budgetUsagePercent > 100 ? 'bg-danger' : ($budgetUsagePercent > 80 ? 'bg-warning' : 'bg-primary') }}" 
             role="progressbar" 
             style="width: {{ min(100, $budgetUsagePercent) }}%" 
             aria-valuenow="{{ $budgetUsagePercent }}" 
             aria-valuemin="0" 
             aria-valuemax="100"></div>
    </div>
    @if($budgetUsagePercent > 100)
        <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small rounded-2">
            <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i> <strong>Over-budget warning!</strong> You have exceeded your monthly budget limit.
        </div>
    @endif
</div>

<!-- Recent Transactions -->
<div class="card card-custom p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Transactions</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                View All History &rarr;
            </a>
        </div>
    </div>

    @if(count($recentTransactions) > 0)
        <div class="list-group list-group-flush">
            @foreach($recentTransactions as $tx)
                <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background-color: {{ $tx['color'] }}20; color: {{ $tx['color'] }};">
                            <i class="bi {{ $tx['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $tx['title'] }}</div>
                            <div class="text-muted small">
                                <span>{{ $tx['date'] }}</span> &bull; <span>{{ $tx['payment_method'] }}</span>
                                @if(!empty($tx['transport_mode']))
                                    &bull; <span class="badge {{ $tx['transport_mode'] === 'PickMe' ? 'bg-warning text-dark border border-warning' : ($tx['transport_mode'] === 'Uber' ? 'bg-dark text-white' : 'bg-primary-subtle text-primary border border-primary-subtle') }} rounded-pill" style="font-size: 0.68rem;">
                                        {{ $tx['transport_mode'] }}{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                    </span>
                                @endif
                                @if($tx['note'])
                                    &bull; <em>{{ Str::limit($tx['note'], 25) }}</em>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold @if($tx['type'] == 'income') text-success @elseif($tx['type'] == 'expense') text-danger @else {{ !empty($tx['is_inflow']) ? 'text-info' : 'text-warning' }} @endif">
                            {{ ($tx['type'] == 'income' || !empty($tx['is_inflow'])) ? '+' : '-' }} Rs. {{ number_format($tx['amount'], 2) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <div class="text-muted mb-2"><i class="bi bi-receipt-cutoff display-6"></i></div>
            <p class="text-muted small mb-2">No transactions recorded yet for this period.</p>
            <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#globalAddIncomeModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Income
                </button>
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Expense
                </button>
            </div>
        </div>
    @endif
</div>
@endsection
