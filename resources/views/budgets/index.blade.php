@extends('layouts.app')

@section('title', 'Monthly Budgets')

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-12 col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-pie-chart text-primary me-2"></i>Budget Management</h4>
        <p class="text-muted small mb-0">Set spending limits and prevent over-spending each month.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2">
        <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 border">
            <i class="bi bi-calendar-event text-primary"></i>
            <span class="small fw-semibold text-muted">Month:</span>
            <input type="month" class="form-control form-control-sm border-0 fw-bold p-0" style="width: auto;" value="{{ $selectedMonth }}" onchange="window.location.href='?month=' + this.value;">
        </div>
        <button class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#setBudgetModal">
            <i class="bi bi-gear-fill me-1"></i> Set Budget
        </button>
    </div>
</div>

<!-- Overall Budget Overview Card -->
<div class="card card-custom p-4 mb-4">
    <div class="row g-3 align-items-center mb-3">
        <div class="col-6 col-md-3">
            <span class="small text-muted fw-semibold">Monthly Budget</span>
            <h4 class="fw-bold mb-0 text-dark">Rs. {{ number_format($overallBudgetAmount, 2) }}</h4>
        </div>
        <div class="col-6 col-md-3">
            <span class="small text-muted fw-semibold">Amount Spent</span>
            <h4 class="fw-bold mb-0 text-danger">Rs. {{ number_format($totalSpent, 2) }}</h4>
        </div>
        <div class="col-6 col-md-3">
            <span class="small text-muted fw-semibold">Remaining</span>
            <h4 class="fw-bold mb-0 {{ $remainingBudget >= 0 ? 'text-success' : 'text-danger' }}">
                Rs. {{ number_format($remainingBudget, 2) }}
            </h4>
        </div>
        <div class="col-6 col-md-3 text-md-end">
            <span class="small text-muted fw-semibold">Budget Used</span>
            <h4 class="fw-bold mb-0 {{ $usagePercent > 100 ? 'text-danger' : ($usagePercent > 80 ? 'text-warning' : 'text-primary') }}">
                {{ $usagePercent }}%
            </h4>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress mb-2" style="height: 12px; border-radius: 8px;">
        <div class="progress-bar {{ $usagePercent > 100 ? 'bg-danger' : ($usagePercent > 80 ? 'bg-warning' : 'bg-primary') }}"
             role="progressbar"
             style="width: {{ min(100, $usagePercent) }}%"
             aria-valuenow="{{ $usagePercent }}"
             aria-valuemin="0"
             aria-valuemax="100"></div>
    </div>

    @if($overallBudgetAmount > 0 && $totalSpent > $overallBudgetAmount)
        <div class="alert alert-danger py-2 px-3 mb-0 small rounded-3 mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Over-Budget Warning!</strong> You have spent Rs. {{ number_format($totalSpent - $overallBudgetAmount, 2) }} more than your monthly budget limit!
        </div>
    @elseif($overallBudgetAmount == 0)
        <div class="alert alert-info py-2 px-3 mb-0 small rounded-3 mt-3">
            <i class="bi bi-info-circle-fill me-2"></i>
            No budget has been set for this month yet. Click <strong>"Set Budget"</strong> to configure your monthly targets.
        </div>
    @endif
</div>

<!-- Category Budgets Section -->
<h5 class="fw-bold mb-3"><i class="bi bi-tags me-2 text-primary"></i>Category Budgets & Spending</h5>

<div class="row g-3">
    @foreach($categoryStats as $stat)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-custom p-3 h-100 {{ $stat['is_over_budget'] ? 'border-danger' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-card-icon" style="width: 38px; height: 38px; background-color: {{ $stat['category']->color }}20; color: {{ $stat['category']->color }};">
                            <i class="bi {{ $stat['category']->icon }}"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">
                                @if($stat['category']->parent)
                                    <span class="text-muted small fw-normal">{{ $stat['category']->parent->name }} ›</span>
                                @endif
                                {{ $stat['category']->name }}
                            </h6>
                            @if($stat['category']->parent)
                                <span class="badge bg-primary-subtle text-primary border rounded-pill small" style="font-size: 0.62rem;">Subcategory</span>
                            @endif
                        </div>
                    </div>
                    @if($stat['is_over_budget'])
                        <span class="badge bg-danger rounded-pill px-2 py-1 small">Over Budget</span>
                    @elseif($stat['allocated'] > 0)
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">{{ $stat['usage_percent'] }}% used</span>
                    @else
                        <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">No Limit</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Spent: <strong class="text-danger">Rs. {{ number_format($stat['spent'], 2) }}</strong></span>
                    <span>Budget: <strong>{{ $stat['allocated'] > 0 ? 'Rs. ' . number_format($stat['allocated'], 2) : 'Not set' }}</strong></span>
                </div>

                @if($stat['allocated'] > 0)
                    <div class="progress mb-2" style="height: 7px; border-radius: 4px;">
                        <div class="progress-bar {{ $stat['usage_percent'] > 100 ? 'bg-danger' : ($stat['usage_percent'] > 80 ? 'bg-warning' : 'bg-primary') }}"
                             style="width: {{ min(100, $stat['usage_percent']) }}%"></div>
                    </div>
                    <div class="small text-end {{ $stat['remaining'] < 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ $stat['remaining'] >= 0 ? 'Remaining: Rs. ' . number_format($stat['remaining'], 2) : 'Exceeded by: Rs. ' . number_format(abs($stat['remaining']), 2) }}
                    </div>
                @else
                    <div class="small text-muted fst-italic">Set a category limit to track progress.</div>
                @endif

                @if($stat['category']->subcategories->count() > 0)
                    <div class="mt-2 pt-2 border-top small text-muted d-flex flex-wrap gap-1 align-items-center">
                        <span style="font-size: 0.7rem;"><i class="bi bi-diagram-2 me-1"></i>Sub-items:</span>
                        @foreach($stat['category']->subcategories as $sub)
                            <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.65rem;">{{ $sub->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<!-- Set Budget Modal -->
<div class="modal fade" id="setBudgetModal" tabindex="-1" aria-labelledby="setBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="setBudgetModalLabel">
                    <i class="bi bi-piggy-bank-fill text-primary me-2"></i>Configure Monthly Budget
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('budgets.store') }}">
                @csrf
                <div class="modal-body pt-3">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted">Select Target Month *</label>
                            <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted">Overall Monthly Budget (Rs.) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-primary">Rs.</span>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control fw-bold fs-5" value="{{ $overallBudgetAmount }}" placeholder="e.g. 60000" required>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2"><i class="bi bi-sliders me-1 text-primary"></i>Optional Category Budgets</h6>
                    <p class="text-muted small mb-3">Set individual spending limits for each category (e.g. Food: Rs. 15,000, Fuel: Rs. 10,000):</p>

                    <div class="row g-2" style="max-height: 320px; overflow-y: auto;">
                        @foreach($categories as $category)
                            <div class="col-12 col-md-6">
                                <div class="p-2 border rounded-3 bg-light d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                                        <i class="bi {{ $category->icon }}" style="color: {{ $category->color }};"></i>
                                        <span class="small fw-semibold text-truncate">{{ $category->name }}</span>
                                    </div>
                                    <div class="input-group input-group-sm" style="max-width: 150px;">
                                        <span class="input-group-text py-0 px-2 bg-white">Rs.</span>
                                        <input type="number" step="0.01" min="0" name="category_budgets[{{ $category->id }}]" class="form-control py-1 px-2" value="{{ $categoryBudgets[$category->id] ?? '' }}" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check2 me-1"></i> Save Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
