@extends('layouts.app')

@section('title', 'Recurring Expenses')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-repeat text-warning me-2"></i>Recurring Expenses & Bills</h4>
        <p class="text-muted small mb-0">Manage regular fixed expenses like rent, utilities, wifi, and subscriptions.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addRecurringModal">
        <i class="bi bi-plus-lg"></i>
        <span>New Recurring Expense</span>
    </button>
</div>

<!-- Overview Metric -->
<div class="card card-custom p-3 mb-4 bg-light border-0">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <span class="small text-muted fw-semibold">Estimated Monthly Fixed Commitment</span>
            <h4 class="fw-bold text-dark mb-0 mt-1">Rs. {{ number_format($totalMonthlyCommitment, 2) }}</h4>
        </div>
        <div class="stat-card-icon bg-warning-subtle text-warning">
            <i class="bi bi-calendar-range"></i>
        </div>
    </div>
</div>

@if(count($recurringExpenses) > 0)
    <div class="row g-3">
        @foreach($recurringExpenses as $item)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-custom p-3 h-100 {{ !$item->is_active ? 'opacity-75' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card-icon" style="width: 36px; height: 36px; background-color: {{ $item->category->color }}20; color: {{ $item->category->color }};">
                                <i class="bi {{ $item->category->icon }}"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->name }}</h6>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $item->category->name }}</small>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('recurring.toggle', $item->id) }}">
                            @csrf
                            <button type="submit" class="badge rounded-pill border-0 {{ $item->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}" style="cursor: pointer;">
                                {{ $item->is_active ? 'Active' : 'Paused' }}
                            </button>
                        </form>
                    </div>

                    <div class="my-2">
                        <span class="fs-5 fw-bold text-dark">Rs. {{ number_format($item->amount, 2) }}</span>
                        <span class="badge bg-light text-muted border text-capitalize ms-1">{{ $item->frequency }}</span>
                    </div>

                    <div class="small text-muted mb-3" style="font-size: 0.78rem;">
                        <div><i class="bi bi-credit-card me-1"></i> {{ $item->payment_method }}</div>
                        <div><i class="bi bi-calendar-check me-1"></i> Started: {{ $item->start_date->format('M d, Y') }}</div>
                        @if($item->last_generated_date)
                            <div><i class="bi bi-clock-history me-1"></i> Last paid: {{ $item->last_generated_date->format('M d, Y') }}</div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                        <form method="POST" action="{{ route('recurring.record', $item->id) }}" class="flex-grow-1 me-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100 rounded-3">
                                <i class="bi bi-plus-circle me-1"></i> Pay Now (Rs. {{ number_format($item->amount, 2) }})
                            </button>
                        </form>
                        <form method="POST" action="{{ route('recurring.destroy', $item->id) }}" onsubmit="return confirm('Delete this recurring expense?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger rounded-3" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <div class="text-muted mb-3"><i class="bi bi-repeat display-5 text-warning"></i></div>
        <h5 class="fw-bold">No Recurring Expenses Found</h5>
        <p class="text-muted small mb-3">Add monthly bills like internet, rent, electricity, gym, or subscriptions.</p>
        <div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRecurringModal">
                <i class="bi bi-plus-lg me-1"></i> Add Recurring Expense
            </button>
        </div>
    </div>
@endif

<!-- Add Recurring Expense Modal -->
<div class="modal fade" id="addRecurringModal" tabindex="-1" aria-labelledby="addRecurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addRecurringModalLabel">
                    <i class="bi bi-repeat text-warning me-2"></i>New Recurring Expense
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('recurring.store') }}">
                @csrf
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Expense Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Home Fiber WiFi, Apartment Rent" required autofocus>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Amount (Rs.) *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Frequency *</label>
                            <select name="frequency" class="form-select" required>
                                <option value="monthly" selected>Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Category *</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Payment Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Bank" selected>Bank Transfer</option>
                                <option value="Online Payment">Online Payment</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Cash">Cash</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">End Date (Optional)</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check2 me-1"></i> Save Recurring Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
