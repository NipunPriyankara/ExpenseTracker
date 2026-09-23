@extends('layouts.app')

@section('title', 'Loan & Debt Manager')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-wallet-fill text-primary me-2"></i>Loan & Debt Manager
        </h4>
        <p class="text-muted small mb-0">Track money borrowed from others and money lent to others with flexible partial and full repayments.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addLoanModal">
        <i class="bi bi-plus-lg"></i>
        <span>Add Loan / Debt</span>
    </button>
</div>

<!-- Financial Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Borrowed Summary (I Owe) -->
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 h-100 border-start border-danger border-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-danger fw-bold text-uppercase">
                    <i class="bi bi-box-arrow-in-down-left me-1"></i> I Borrowed (මම ගත් ණය)
                </span>
                <span class="badge bg-danger-subtle text-danger">{{ $countActiveBorrowed }} Active</span>
            </div>
            <h4 class="fw-bold text-danger mt-1 mb-1">Rs. {{ number_format($remainingDebt, 2) }}</h4>
            <div class="small text-muted d-flex justify-content-between mt-2 pt-2 border-top">
                <span>Total Borrowed: <strong>Rs. {{ number_format($totalBorrowed, 2) }}</strong></span>
                <span>Repaid: <strong class="text-success">Rs. {{ number_format($totalRepaidBorrowed, 2) }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Lent Summary (Owed to Me) -->
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 h-100 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-success fw-bold text-uppercase">
                    <i class="bi bi-box-arrow-up-right me-1"></i> I Lent (මම දුන් ණය)
                </span>
                <span class="badge bg-success-subtle text-success">{{ $countActiveLent }} Active</span>
            </div>
            <h4 class="fw-bold text-success mt-1 mb-1">Rs. {{ number_format($remainingReceivable, 2) }}</h4>
            <div class="small text-muted d-flex justify-content-between mt-2 pt-2 border-top">
                <span>Total Lent: <strong>Rs. {{ number_format($totalLent, 2) }}</strong></span>
                <span>Collected: <strong class="text-success">Rs. {{ number_format($totalCollectedLent, 2) }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Settlement Overview -->
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 h-100 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-primary fw-bold text-uppercase">
                    <i class="bi bi-check2-all me-1"></i> Settle Overview
                </span>
                <span class="badge bg-primary-subtle text-primary">{{ $countSettled }} Settled</span>
            </div>
            <div class="mt-2">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Active Borrowings (I Owe):</span>
                    <strong class="text-danger">{{ $countActiveBorrowed }}</strong>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Active Lendings (Owed to Me):</span>
                    <strong class="text-success">{{ $countActiveLent }}</strong>
                </div>
                <div class="d-flex justify-content-between small text-muted border-top pt-1">
                    <span>Fully Settled Records:</span>
                    <strong class="text-primary">{{ $countSettled }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation Filter -->
<div class="card card-custom p-2 mb-4">
    <ul class="nav nav-pills nav-fill gap-1">
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $tab === 'all' ? 'active shadow-sm' : 'text-muted' }}" href="{{ route('loans.index', ['tab' => 'all']) }}">
                <i class="bi bi-grid-fill me-1"></i> All Loans
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $tab === 'borrowed' ? 'active bg-danger shadow-sm' : 'text-muted' }}" href="{{ route('loans.index', ['tab' => 'borrowed']) }}">
                <i class="bi bi-box-arrow-in-down-left me-1"></i> I Borrowed
                @if($countActiveBorrowed > 0)
                    <span class="badge bg-light text-danger ms-1">{{ $countActiveBorrowed }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $tab === 'lent' ? 'active bg-success shadow-sm' : 'text-muted' }}" href="{{ route('loans.index', ['tab' => 'lent']) }}">
                <i class="bi bi-box-arrow-up-right me-1"></i> I Lent
                @if($countActiveLent > 0)
                    <span class="badge bg-light text-success ms-1">{{ $countActiveLent }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2 rounded-3 {{ $tab === 'settled' ? 'active bg-primary shadow-sm' : 'text-muted' }}" href="{{ route('loans.index', ['tab' => 'settled']) }}">
                <i class="bi bi-check2-circle me-1"></i> Fully Settled
                @if($countSettled > 0)
                    <span class="badge bg-light text-primary ms-1">{{ $countSettled }}</span>
                @endif
            </a>
        </li>
    </ul>
</div>

<!-- Loan Cards Grid -->
@if(count($loans) > 0)
    <div class="row g-3">
        @foreach($loans as $loan)
            <div class="col-12 col-md-6">
                <div class="card card-custom p-4 h-100 d-flex flex-column">
                    <!-- Top Badge and Actions -->
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            @if($loan->isBorrowed())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-box-arrow-in-down-left me-1"></i> Borrowed (ණය ගත්තා)
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Lent (ණය දුන්නා)
                                </span>
                            @endif

                            @if($loan->isSettled())
                                <span class="badge bg-primary text-white px-2 py-1">
                                    <i class="bi bi-check2-all me-1"></i> Fully Settled
                                </span>
                            @endif
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                <li>
                                    <button type="button" class="dropdown-item small d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#editLoanModal{{ $loan->id }}">
                                        <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Loan Details
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form method="POST" action="{{ route('loans.destroy', $loan->id) }}" onsubmit="return confirm('Delete this entire loan record and all payment history?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger small d-flex align-items-center">
                                            <i class="bi bi-trash me-2"></i> Delete Loan Record
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Person & Loan Purpose -->
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-1">{{ $loan->person_name ?: $loan->name }}</h5>
                        @if($loan->name && $loan->person_name && $loan->name !== $loan->person_name)
                            <div class="text-muted small">{{ $loan->name }}</div>
                        @endif
                        <div class="text-muted small mt-1">
                            <i class="bi bi-calendar3 me-1"></i> Started: {{ $loan->start_date ? $loan->start_date->format('M d, Y') : 'N/A' }}
                            @if($loan->end_date)
                                <span class="ms-2"><i class="bi bi-clock-history me-1"></i> Due: {{ $loan->end_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Financial Balance Box -->
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-4">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Total Amount</span>
                                <strong class="text-dark">Rs. {{ number_format($loan->total_amount, 2) }}</strong>
                            </div>
                            <div class="col-4 text-center">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Paid / Repaid</span>
                                <strong class="text-success">Rs. {{ number_format($loan->paid_amount, 2) }}</strong>
                            </div>
                            <div class="col-4 text-end">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Remaining</span>
                                <strong class="{{ $loan->remaining_amount > 0 ? ($loan->isBorrowed() ? 'text-danger' : 'text-primary') : 'text-muted' }}">
                                    Rs. {{ number_format($loan->remaining_amount, 2) }}
                                </strong>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-2">
                            <div class="progress" style="height: 7px; border-radius: 4px;">
                                <div class="progress-bar {{ $loan->isSettled() ? 'bg-primary' : ($loan->isBorrowed() ? 'bg-danger' : 'bg-success') }}" 
                                     style="width: {{ $loan->paid_percentage }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.72rem;">
                                <span>{{ $loan->paid_percentage }}% Settled</span>
                                <span>{{ count($loan->payments) }} Payment{{ count($loan->payments) == 1 ? '' : 's' }} recorded</span>
                            </div>
                        </div>
                    </div>

                    @if($loan->notes)
                        <div class="text-muted small bg-light-subtle border border-light-subtle rounded-2 p-2 mb-3" style="font-size: 0.8rem;">
                            <i class="bi bi-card-text me-1"></i> {{ $loan->notes }}
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-2 border-top mt-auto">
                        @if(!$loan->isSettled())
                            <button class="btn btn-success text-white btn-sm rounded-pill flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-1 py-2" 
                                    data-bs-toggle="modal" data-bs-target="#paymentModal{{ $loan->id }}">
                                <i class="bi bi-credit-card-2-front"></i>
                                <span>Record Payment / Settle</span>
                            </button>
                        @else
                            <button class="btn btn-light btn-sm rounded-pill flex-grow-1 text-muted border py-2" disabled>
                                <i class="bi bi-check2-all text-primary me-1"></i> Fully Settled
                            </button>
                        @endif

                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1 py-2" 
                                data-bs-toggle="modal" data-bs-target="#historyModal{{ $loan->id }}">
                            <i class="bi bi-clock-history"></i>
                            <span>History ({{ count($loan->payments) }})</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Record Payment / Settle Modal -->
            <div class="modal fade" id="paymentModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-cash-coin text-success me-2"></i>
                                {{ $loan->isBorrowed() ? 'Pay Repayment to' : 'Collect Payment from' }} {{ $loan->person_name ?: $loan->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('loans.payment', $loan->id) }}">
                            @csrf
                            <div class="modal-body pt-3">
                                <!-- Balance Alert & 1-Click Settle Button -->
                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block">Current Remaining Balance</small>
                                            <h5 class="fw-bold text-dark mb-0">Rs. {{ number_format($loan->remaining_amount, 2) }}</h5>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                onclick="document.getElementById('amountInput{{ $loan->id }}').value='{{ $loan->remaining_amount }}';">
                                            <i class="bi bi-check2-all me-1"></i> Settle Full Balance
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">
                                        Payment Amount (Rs.) * <small class="text-muted">(Enter partial amount or full balance)</small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">Rs.</span>
                                        <input type="number" step="0.01" min="0.01" max="{{ $loan->remaining_amount }}" 
                                               id="amountInput{{ $loan->id }}" name="amount" class="form-control fw-bold fs-5 border-start-0 ps-1" 
                                               placeholder="{{ $loan->monthly_installment ?: $loan->remaining_amount }}" 
                                               value="{{ $loan->monthly_installment ?: '' }}" required>
                                    </div>
                                    <small class="text-muted">You can pay any partial amount (e.g. Rs. 1,000).</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Payment Date *</label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Note (Optional)</label>
                                    <input type="text" name="note" class="form-control" placeholder="e.g. Partial payment 1, Cash installment">
                                </div>

                                @if($loan->isBorrowed())
                                    <div class="form-check p-3 bg-light-subtle border rounded-3 mb-2">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="record_transaction" value="1" id="recordTrans{{ $loan->id }}">
                                        <label class="form-check-label small" for="recordTrans{{ $loan->id }}">
                                            Sync to Monthly Tracker as a <strong>Debt Repayment Expense</strong> (Cash Outflow)
                                        </label>
                                    </div>
                                @else
                                    <div class="p-3 bg-info-subtle border border-info-subtle rounded-3 mb-2 text-info-emphasis small d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-check fs-5 text-info"></i>
                                        <div>
                                            <strong>Accounting Standard (GAAP):</strong> This settlement recovers your cash asset without double-counting your earned income.
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success text-white rounded-3 px-4 shadow-sm">
                                    <i class="bi bi-check2 me-1"></i> Save Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Payment History Modal -->
            <div class="modal fade" id="historyModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold">Payment History</h5>
                                <div class="text-muted small">{{ $loan->person_name ?: $loan->name }} (Total: Rs. {{ number_format($loan->total_amount, 2) }})</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-3">
                            @if(count($loan->payments) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($loan->payments as $p)
                                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-success fs-6">+ Rs. {{ number_format($p->amount, 2) }}</div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-calendar3 me-1"></i> {{ $p->payment_date ? $p->payment_date->format('M d, Y') : 'N/A' }}
                                                    @if($p->note)
                                                        <span class="ms-1 text-dark">• {{ $p->note }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <form method="POST" action="{{ route('loans.payment.destroy', $p->id) }}" onsubmit="return confirm('Delete this payment entry? The loan remaining balance will be restored.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Delete Payment Entry">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                    No payments recorded yet for this loan.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Loan / Debt Modal -->
            <div class="modal fade" id="editLoanModal{{ $loan->id }}" tabindex="-1" aria-labelledby="editLoanModalLabel{{ $loan->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="editLoanModalLabel{{ $loan->id }}">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Loan Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('loans.update', $loan->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body pt-3">
                                <!-- Loan Type Toggle -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted d-block">Loan Type *</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="type" id="editTypeBorrowed{{ $loan->id }}" value="borrowed" {{ $loan->type === 'borrowed' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-danger w-100 rounded-3 py-2 text-start small d-flex align-items-center gap-2" for="editTypeBorrowed{{ $loan->id }}">
                                                <i class="bi bi-box-arrow-in-down-left fs-5"></i>
                                                <div>
                                                    <strong class="d-block">I Borrowed</strong>
                                                    <span class="text-muted" style="font-size: 0.7rem;">මම ගත් ණය</span>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="type" id="editTypeLent{{ $loan->id }}" value="lent" {{ $loan->type === 'lent' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success w-100 rounded-3 py-2 text-start small d-flex align-items-center gap-2" for="editTypeLent{{ $loan->id }}">
                                                <i class="bi bi-box-arrow-up-right fs-5"></i>
                                                <div>
                                                    <strong class="d-block">I Lent</strong>
                                                    <span class="text-muted" style="font-size: 0.7rem;">මම දුන් ණය</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Person Name -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Person Name (පුද්ගලයාගේ නම) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                        <input type="text" name="person_name" class="form-control border-start-0 ps-0" value="{{ $loan->person_name }}" required>
                                    </div>
                                </div>

                                <!-- Purpose / Loan Title -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Purpose / Title (Optional)</label>
                                    <input type="text" name="name" class="form-control" value="{{ $loan->name }}" placeholder="e.g. Urgent Need, Medical, Vehicle Advance">
                                </div>

                                <!-- Total Amount & Start Date -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted">Total Amount (Rs.) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rs.</span>
                                            <input type="number" step="0.01" min="{{ max(0.01, (float)$loan->paid_amount) }}" name="total_amount" class="form-control fw-bold border-start-0 ps-1" value="{{ $loan->total_amount }}" required>
                                        </div>
                                        @if((float)$loan->paid_amount > 0)
                                            <div class="text-muted" style="font-size: 0.68rem;">Min: Rs. {{ number_format($loan->paid_amount, 2) }} (already paid)</div>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted">Date Given / Taken *</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $loan->start_date ? $loan->start_date->format('Y-m-d') : '' }}" required>
                                    </div>
                                </div>

                                <!-- Optional Installment & Due Date -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted">Planned Installment (Optional)</label>
                                        <input type="number" step="0.01" min="0" name="monthly_installment" class="form-control" value="{{ $loan->monthly_installment }}" placeholder="e.g. 1000.00">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted">Expected Due Date (Optional)</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ $loan->end_date ? $loan->end_date->format('Y-m-d') : '' }}">
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-muted">Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Any agreement, repayment terms, or notes...">{{ $loan->notes }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                    <i class="bi bi-check2 me-1"></i> Update Loan Details
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <div class="text-muted mb-3"><i class="bi bi-wallet2 display-4 text-primary"></i></div>
        <h5 class="fw-bold">No Records Found</h5>
        <p class="text-muted small mb-3">
            @if($tab === 'borrowed')
                You have no active loans borrowed from individuals or banks.
            @elseif($tab === 'lent')
                You have not recorded any money lent out to other people.
            @elseif($tab === 'settled')
                You have no fully settled loans yet.
            @else
                Start tracking money you borrowed or lent to friends, relatives, or banks.
            @endif
        </p>
        <div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                <i class="bi bi-plus-lg me-1"></i> Add First Loan / Debt
            </button>
        </div>
    </div>
@endif

<!-- Add Loan / Debt Modal -->
<div class="modal fade" id="addLoanModal" tabindex="-1" aria-labelledby="addLoanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addLoanModalLabel">
                    <i class="bi bi-wallet-fill text-primary me-2"></i>New Loan / Debt Tracker
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('loans.store') }}">
                @csrf
                <div class="modal-body pt-3">
                    <!-- Loan Type Toggle -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted d-block">Loan Type *</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="typeBorrowed" value="borrowed" checked onchange="updateTypeLabels()">
                                <label class="btn btn-outline-danger w-100 rounded-3 py-2 text-start small d-flex align-items-center gap-2" for="typeBorrowed">
                                    <i class="bi bi-box-arrow-in-down-left fs-5"></i>
                                    <div>
                                        <strong class="d-block">I Borrowed</strong>
                                        <span class="text-muted" style="font-size: 0.7rem;">මම ගත් ණය</span>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="typeLent" value="lent" onchange="updateTypeLabels()">
                                <label class="btn btn-outline-success w-100 rounded-3 py-2 text-start small d-flex align-items-center gap-2" for="typeLent">
                                    <i class="bi bi-box-arrow-up-right fs-5"></i>
                                    <div>
                                        <strong class="d-block">I Lent</strong>
                                        <span class="text-muted" style="font-size: 0.7rem;">මම දුන් ණය</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Person Name -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted" id="personNameLabel">Lender Name (කාගෙන්ද ගත්තේ / Person B) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="person_name" class="form-control border-start-0 ps-0" placeholder="e.g. Person B, Kamal, BOC Bank" required autofocus>
                        </div>
                    </div>

                    <!-- Purpose / Loan Title -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Purpose / Title (Optional)</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Urgent Need, Medical, Vehicle Advance">
                    </div>

                    <!-- Total Amount & Start Date -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Total Amount (Rs.) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rs.</span>
                                <input type="number" step="0.01" min="0.01" name="total_amount" class="form-control fw-bold border-start-0 ps-1" placeholder="20000.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Date Given / Taken *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Optional Installment & Due Date -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Planned Installment (Optional)</label>
                            <input type="number" step="0.01" min="0" name="monthly_installment" class="form-control" placeholder="e.g. 1000.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Expected Due Date (Optional)</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any agreement, repayment terms, or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check2 me-1"></i> Save Loan Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTypeLabels() {
    const isBorrowed = document.getElementById('typeBorrowed').checked;
    const label = document.getElementById('personNameLabel');
    if (isBorrowed) {
        label.innerText = 'Lender Name (කාගෙන්ද ණය ගත්තේ / Person B) *';
    } else {
        label.innerText = 'Borrower Name (කාටද ණය දුන්නේ / Whom you gave) *';
    }
}
</script>
@endsection
