@extends('layouts.app')

@section('title', 'Savings & Goal Tracker')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-piggy-bank-fill text-success me-2"></i>Savings & Goal Tracker
        </h4>
        <p class="text-muted small mb-0">Build emergency funds, track targeted savings, and manage deposits and withdrawals with dates.</p>
    </div>
    <button class="btn btn-success text-white rounded-pill px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addGoalModal">
        <i class="bi bi-plus-lg"></i>
        <span>New Savings Goal</span>
    </button>
</div>

<!-- Savings Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Saved -->
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 h-100 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-success fw-bold text-uppercase">
                    <i class="bi bi-wallet2 me-1"></i> Total Saved (මුළු ඉතිරිය)
                </span>
                <span class="badge bg-success-subtle text-success">{{ $countActive }} Active</span>
            </div>
            <h3 class="fw-bold text-success mt-1 mb-0">Rs. {{ number_format($totalSaved, 2) }}</h3>
            <small class="text-muted" style="font-size: 0.75rem;">Total accumulated across all funds</small>
        </div>
    </div>

    <!-- Total Target Needed -->
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 h-100 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-primary fw-bold text-uppercase">
                    <i class="bi bi-bullseye me-1"></i> Total Target (මුළු ඉලක්කය)
                </span>
                <span class="badge bg-primary-subtle text-primary">{{ $countCompleted }} Achieved</span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Rs. {{ number_format($totalTarget, 2) }}</h3>
            <small class="text-muted" style="font-size: 0.75rem;">
                Remaining to save: <strong class="text-primary">Rs. {{ number_format(max(0, $totalTarget - $totalSaved), 2) }}</strong>
            </small>
        </div>
    </div>

    <!-- Overall Progress -->
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 h-100 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-warning fw-bold text-uppercase">
                    <i class="bi bi-graph-up-arrow me-1"></i> Overall Progress
                </span>
                <span class="badge bg-warning-subtle text-dark fw-bold">{{ $overallProgress }}%</span>
            </div>
            <div class="mt-2">
                <div class="progress" style="height: 10px; border-radius: 6px;">
                    <div class="progress-bar bg-success" style="width: {{ $overallProgress }}%"></div>
                </div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-2 pt-1 border-top" style="font-size: 0.75rem;">
                <span>Active Goals: <strong class="text-dark">{{ $countActive }}</strong></span>
                <span>Completed: <strong class="text-success">{{ $countCompleted }}</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation Filter -->
<div class="card card-custom p-2 mb-4">
    <ul class="nav nav-pills nav-fill gap-1">
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $statusFilter === 'all' ? 'active shadow-sm' : 'text-muted' }}" href="{{ route('savings.index', ['status' => 'all']) }}">
                <i class="bi bi-grid-fill me-1"></i> All Goals
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $statusFilter === 'active' ? 'active bg-success shadow-sm' : 'text-muted' }}" href="{{ route('savings.index', ['status' => 'active']) }}">
                <i class="bi bi-hourglass-split me-1"></i> In Progress
                @if($countActive > 0)
                    <span class="badge bg-light text-success ms-1">{{ $countActive }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $statusFilter === 'completed' ? 'active bg-primary shadow-sm' : 'text-muted' }}" href="{{ route('savings.index', ['status' => 'completed']) }}">
                <i class="bi bi-trophy-fill me-1"></i> Goals Achieved
                @if($countCompleted > 0)
                    <span class="badge bg-light text-primary ms-1">{{ $countCompleted }}</span>
                @endif
            </a>
        </li>
    </ul>
</div>

<!-- Savings Goals Grid -->
@if(count($goals) > 0)
    <div class="row g-3">
        @foreach($goals as $goal)
            <div class="col-12 col-md-6">
                <div class="card card-custom p-4 h-100 d-flex flex-column" style="border-top: 4px solid {{ $goal->color ?: '#10b981' }};">
                    <!-- Top Badge and Actions -->
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card-icon text-white shadow-sm" style="background-color: {{ $goal->color ?: '#10b981' }}; width: 40px; height: 40px; border-radius: 10px;">
                                <i class="bi {{ $goal->icon ?: 'bi-piggy-bank' }} fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ $goal->name }}</h5>
                                @if($goal->target_date)
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-calendar-event me-1"></i> Target: {{ $goal->target_date->format('M d, Y') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            @if($goal->isCompleted())
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Achieved
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">
                                    {{ $goal->progress_percentage }}%
                                </span>
                            @endif

                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                    <li>
                                        <form method="POST" action="{{ route('savings.destroy', $goal->id) }}" onsubmit="return confirm('Delete this savings goal and all its transaction history?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger small">
                                                <i class="bi bi-trash me-2"></i> Delete Goal
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Progress Box -->
                    <div class="bg-light rounded-3 p-3 my-3">
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Current Saved</span>
                                <h4 class="fw-bold text-success mb-0">Rs. {{ number_format($goal->current_amount, 2) }}</h4>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Target Goal</span>
                                <span class="fw-bold text-dark fs-6">Rs. {{ number_format($goal->target_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-2">
                            <div class="progress" style="height: 9px; border-radius: 6px;">
                                <div class="progress-bar {{ $goal->isCompleted() ? 'bg-success' : 'bg-primary' }}" 
                                     style="width: {{ $goal->progress_percentage }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.72rem;">
                                <span>{{ $goal->progress_percentage }}% of goal reached</span>
                                <span>
                                    @if($goal->remaining_amount > 0)
                                        Rs. {{ number_format($goal->remaining_amount, 2) }} left
                                    @else
                                        <strong class="text-success">Goal Fully Reached!</strong>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($goal->notes)
                        <div class="text-muted small bg-light-subtle border border-light-subtle rounded-2 p-2 mb-3" style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle me-1"></i> {{ $goal->notes }}
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-2 border-top mt-auto">
                        <button class="btn btn-success text-white btn-sm rounded-pill flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-1 py-2" 
                                data-bs-toggle="modal" data-bs-target="#depositModal{{ $goal->id }}">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Money</span>
                        </button>

                        @if($goal->current_amount > 0)
                            <button class="btn btn-outline-danger btn-sm rounded-pill flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-2" 
                                    data-bs-toggle="modal" data-bs-target="#withdrawModal{{ $goal->id }}">
                                <i class="bi bi-dash-circle"></i>
                                <span>Withdraw</span>
                            </button>
                        @endif

                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1 py-2" 
                                data-bs-toggle="modal" data-bs-target="#historyModal{{ $goal->id }}">
                            <i class="bi bi-clock-history"></i>
                            <span>{{ count($goal->transactions) }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Deposit Modal -->
            <div class="modal fade" id="depositModal{{ $goal->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-piggy-bank text-success me-2"></i>Add to {{ $goal->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('savings.deposit', $goal->id) }}">
                            @csrf
                            <div class="modal-body pt-3">
                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block">Current Saved</small>
                                            <strong class="text-success fs-5">Rs. {{ number_format($goal->current_amount, 2) }}</strong>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Remaining to Goal</small>
                                            <strong class="text-dark">Rs. {{ number_format($goal->remaining_amount, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Deposit Amount (Rs.) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">Rs.</span>
                                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold fs-5 border-start-0 ps-1" 
                                               placeholder="5000.00" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Deposit Date *</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Note (Optional)</label>
                                    <input type="text" name="note" class="form-control" placeholder="e.g. Monthly salary savings, Bonus">
                                </div>

                                <div class="form-check p-3 bg-light-subtle border rounded-3 mb-2">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="record_as_expense" value="1" id="recordExpense{{ $goal->id }}" checked>
                                    <label class="form-check-label small" for="recordExpense{{ $goal->id }}">
                                        Also record this transfer as an <strong>Expense</strong> in my Monthly Tracker
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success text-white rounded-3 px-4 shadow-sm">
                                    <i class="bi bi-check2 me-1"></i> Save Deposit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Withdraw Modal -->
            @if($goal->current_amount > 0)
                <div class="modal fade" id="withdrawModal{{ $goal->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-wallet2 text-danger me-2"></i>Withdraw from {{ $goal->name }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('savings.withdraw', $goal->id) }}">
                                @csrf
                                <div class="modal-body pt-3">
                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <small class="text-muted d-block">Available in Fund</small>
                                        <strong class="text-success fs-4">Rs. {{ number_format($goal->current_amount, 2) }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted">Withdraw Amount (Rs.) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rs.</span>
                                            <input type="number" step="0.01" min="0.01" max="{{ $goal->current_amount }}" name="amount" 
                                                   class="form-control fw-bold fs-5 border-start-0 ps-1" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted">Withdrawal Date *</label>
                                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted">Reason / Note (Optional)</label>
                                        <input type="text" name="note" class="form-control" placeholder="e.g. Medical emergency, Bought planned item">
                                    </div>

                                    <div class="form-check p-3 bg-light-subtle border rounded-3 mb-2">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="record_as_income" value="1" id="recordIncome{{ $goal->id }}">
                                        <label class="form-check-label small" for="recordIncome{{ $goal->id }}">
                                            Also record this withdrawal as <strong>Income</strong> (money returning to wallet)
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger rounded-3 px-4 shadow-sm">
                                        <i class="bi bi-dash-circle me-1"></i> Confirm Withdrawal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Savings History Modal -->
            <div class="modal fade" id="historyModal{{ $goal->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold">Savings History</h5>
                                <div class="text-muted small">{{ $goal->name }} (Target: Rs. {{ number_format($goal->target_amount, 2) }})</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-3">
                            @if(count($goal->transactions) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($goal->transactions as $tx)
                                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold fs-6 {{ $tx->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $tx->type === 'deposit' ? '+' : '-' }} Rs. {{ number_format($tx->amount, 2) }}
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-calendar3 me-1"></i> {{ $tx->transaction_date ? $tx->transaction_date->format('M d, Y') : 'N/A' }}
                                                    @if($tx->note)
                                                        <span class="ms-1 text-dark">• {{ $tx->note }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="badge {{ $tx->type === 'deposit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill">
                                                {{ ucfirst($tx->type) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                    No deposits or withdrawals recorded yet.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <div class="text-muted mb-3"><i class="bi bi-piggy-bank display-3 text-success"></i></div>
        <h5 class="fw-bold">No Savings Goals Set Yet</h5>
        <p class="text-muted small mb-3">
            Create financial targets for emergency funds, vehicle down payments, gadgets, weddings, or vacations.
        </p>
        <div>
            <button class="btn btn-success text-white rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                <i class="bi bi-plus-lg me-1"></i> Create First Savings Goal
            </button>
        </div>
    </div>
@endif

<!-- New Savings Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addGoalModalLabel">
                    <i class="bi bi-piggy-bank-fill text-success me-2"></i>New Savings Goal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('savings.store') }}">
                @csrf
                <div class="modal-body pt-3">
                    <!-- Quick Preset Buttons -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted d-block">Quick Ideas</label>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="fillGoal('Emergency Fund', 100000, 'bi-shield-check', '#10b981')">🛡️ Emergency Fund</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="fillGoal('New Phone / Laptop', 150000, 'bi-laptop', '#4f46e5')">💻 Tech / Gadget</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="fillGoal('Vehicle Down Payment', 500000, 'bi-car-front-fill', '#0284c7')">🚗 Vehicle Fund</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="fillGoal('Vacation & Travel', 80000, 'bi-airplane-fill', '#f59e0b')">✈️ Travel</button>
                        </div>
                    </div>

                    <!-- Goal Name -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Goal Name *</label>
                        <input type="text" name="name" id="goalNameInput" class="form-control" placeholder="e.g. Emergency Fund, New Laptop" required autofocus>
                    </div>

                    <!-- Target Amount & Initial Deposit -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Target Goal (Rs.) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rs.</span>
                                <input type="number" step="0.01" min="1" name="target_amount" id="goalTargetInput" class="form-control fw-bold border-start-0 ps-1" placeholder="50000" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Initial Deposit (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rs.</span>
                                <input type="number" step="0.01" min="0" name="initial_deposit" class="form-control border-start-0 ps-1" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Target Date -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Target Completion Date (Optional)</label>
                        <input type="date" name="target_date" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Icon & Color Customization -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Color</label>
                            <select name="color" id="goalColorInput" class="form-select">
                                <option value="#10b981">🟢 Emerald Green</option>
                                <option value="#4f46e5">🟣 Indigo</option>
                                <option value="#0284c7">🔵 Sky Blue</option>
                                <option value="#f59e0b">🟡 Amber Gold</option>
                                <option value="#ec4899">🌸 Pink</option>
                                <option value="#8b5cf6">🔮 Purple</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Icon</label>
                            <select name="icon" id="goalIconInput" class="form-select">
                                <option value="bi-piggy-bank">Piggy Bank</option>
                                <option value="bi-shield-check">Shield (Safety)</option>
                                <option value="bi-laptop">Laptop / Device</option>
                                <option value="bi-car-front-fill">Car / Vehicle</option>
                                <option value="bi-airplane-fill">Airplane (Travel)</option>
                                <option value="bi-heart-fill">Heart / Wedding</option>
                                <option value="bi-house-fill">House / Land</option>
                            </select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Why you're saving, bank account used, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check2 me-1"></i> Save Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fillGoal(name, amount, icon, color) {
    document.getElementById('goalNameInput').value = name;
    document.getElementById('goalTargetInput').value = amount;
    if (document.getElementById('goalIconInput')) document.getElementById('goalIconInput').value = icon;
    if (document.getElementById('goalColorInput')) document.getElementById('goalColorInput').value = color;
}
</script>
@endsection
