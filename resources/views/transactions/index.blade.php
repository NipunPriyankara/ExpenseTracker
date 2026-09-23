@extends('layouts.app')

@section('title', 'All Transactions')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Unified Transaction History</h4>
        <p class="text-muted small mb-0">Search, filter, and review all combined income and expense transactions.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('transactions.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>
            <span>Export CSV</span>
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label small fw-semibold text-muted">Search Description</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search notes..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">All Types</option>
                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expenses Only</option>
                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income Only</option>
                <option value="settlement" {{ request('type') == 'settlement' ? 'selected' : '' }}>Loan Settlements / Transfers</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted">Category</label>
            <select name="category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted">From Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-muted">To Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3" title="Apply Filter">
                <i class="bi bi-funnel"></i>
            </button>
            @if(request()->hasAny(['search', 'type', 'category_id', 'start_date', 'end_date']))
                <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm rounded-3 border" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Summary Ribbons -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 px-1">
    <div class="small text-muted">
        Showing <strong>{{ $transactions->total() }}</strong> total transactions
    </div>
    <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
            Income: + Rs. {{ number_format($totalIncomeSum, 2) }}
        </span>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
            Expense: - Rs. {{ number_format($totalExpenseSum, 2) }}
        </span>
        @if(($totalSettlementInflow ?? 0) > 0 || ($totalSettlementOutflow ?? 0) > 0)
            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2 rounded-pill">
                <i class="bi bi-arrow-left-right me-1"></i> Settlements: 
                @if(($totalSettlementInflow ?? 0) > 0)+ Rs. {{ number_format($totalSettlementInflow, 2) }} (Recovered)@endif
                @if(($totalSettlementOutflow ?? 0) > 0) - Rs. {{ number_format($totalSettlementOutflow, 2) }} (Debt Paid)@endif
            </span>
        @endif
    </div>
</div>

@if($transactions->count() > 0)
    <!-- Desktop Table View -->
    <div class="card card-custom d-none d-md-block overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th class="ps-3 py-3">DATE</th>
                        <th>TYPE</th>
                        <th>CATEGORY / SOURCE</th>
                        <th>DESCRIPTION</th>
                        <th>PAYMENT METHOD</th>
                        <th class="text-end">AMOUNT</th>
                        <th class="text-center pe-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td class="ps-3 fw-medium text-dark">
                                {{ $tx['date']->format('M d, Y') }}
                            </td>
                            <td>
                                @if($tx['type'] == 'income')
                                    <span class="badge rounded-pill bg-success text-white px-2 py-1 small text-uppercase" style="font-size: 0.68rem;">INCOME</span>
                                @elseif($tx['type'] == 'expense')
                                    <span class="badge rounded-pill bg-danger text-white px-2 py-1 small text-uppercase" style="font-size: 0.68rem;">EXPENSE</span>
                                @else
                                    <span class="badge rounded-pill {{ ($tx['settlement_direction'] ?? '') == 'inflow' ? 'bg-info text-white' : 'bg-warning text-dark' }} px-2 py-1 small text-uppercase" style="font-size: 0.68rem;">
                                        {{ ($tx['settlement_direction'] ?? '') == 'inflow' ? 'SETTLED' : 'DEBT PAID' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($tx['is_split']))
                                    <span class="badge rounded-pill px-2 py-1 text-white shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 0.74rem;">
                                        <i class="bi bi-receipt-cutoff"></i> Split Bill ({{ count($tx['split_items']) }} items)
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $tx['color'] }}15; color: {{ $tx['color'] }}; border: 1px solid {{ $tx['color'] }}30;">
                                        <i class="bi {{ $tx['icon'] }} me-1"></i>
                                        {{ $tx['category'] }}
                                    </span>
                                @endif

                                @if(empty($tx['is_split']) && !empty($tx['transport_mode']))
                                    <div class="mt-1">
                                        @if($tx['transport_mode'] === 'PickMe')
                                            <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-geo-alt-fill text-dark me-1"></i>PickMe{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                            </span>
                                        @elseif($tx['transport_mode'] === 'Uber')
                                            <span class="badge bg-dark text-white" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-car-front-fill text-light me-1"></i>Uber{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                            </span>
                                        @elseif($tx['transport_mode'] === 'Bus')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-bus-front-fill me-1"></i>Bus
                                            </span>
                                        @elseif($tx['transport_mode'] === 'Train')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-train-front-fill me-1"></i>Train
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-compass-fill me-1"></i>{{ $tx['transport_mode'] }}{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($tx['is_split']))
                                    <div class="d-flex flex-column gap-1 my-1" style="min-width: 280px; max-width: 480px;">
                                        @foreach($tx['split_items'] as $sItem)
                                            <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 bg-light border small" style="font-size: 0.77rem;">
                                                <div class="d-flex align-items-center gap-1 text-truncate me-2">
                                                    <span class="badge rounded-pill border py-0 px-2" style="background-color: {{ $sItem['color'] ?? '#4f46e5' }}15; color: {{ $sItem['color'] ?? '#4f46e5' }}; font-size: 0.68rem; font-weight: 600;">
                                                        <i class="bi {{ $sItem['icon'] ?? 'bi-tag' }} me-1"></i>{{ $sItem['category_name'] }}
                                                    </span>
                                                    <span class="text-dark fw-medium text-truncate">{{ $sItem['note'] ?: 'Item' }}</span>
                                                </div>
                                                <span class="fw-bold text-danger text-nowrap">Rs. {{ number_format($sItem['amount'], 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">{{ $tx['note'] ?: '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-2 px-2 py-1 small">
                                    {{ $tx['payment_method'] }}
                                </span>
                            </td>
                            <td class="text-end fw-bold @if($tx['type'] == 'income') text-success @elseif($tx['type'] == 'expense') text-danger @else {{ ($tx['settlement_direction'] ?? '') == 'inflow' ? 'text-info' : 'text-warning' }} @endif">
                                {{ ($tx['type'] == 'income' || ($tx['settlement_direction'] ?? '') == 'inflow') ? '+' : '-' }} Rs. {{ number_format($tx['amount'], 2) }}
                            </td>
                            <td class="text-center pe-3">
                                @if($tx['type'] === 'expense')
                                    <a href="{{ route('expenses.edit', $tx['id']) }}" class="btn btn-sm btn-outline-secondary rounded-2 py-0 px-2" title="Edit Expense">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @elseif($tx['type'] === 'income')
                                    <a href="{{ route('incomes.edit', $tx['id']) }}" class="btn btn-sm btn-outline-secondary rounded-2 py-0 px-2" title="Edit Income">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @elseif($tx['type'] === 'settlement')
                                    <a href="{{ route('loans.index') }}" class="btn btn-sm btn-outline-secondary rounded-2 py-0 px-2" title="View Loan">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards View -->
    <div class="d-md-none mb-4">
        @foreach($transactions as $tx)
            @if(!empty($tx['is_split']))
                <div class="card card-custom p-3 mb-2 border-start border-4" style="border-left-color: #4f46e5 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-2 py-1 text-white shadow-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 0.72rem;">
                                <i class="bi bi-receipt-cutoff me-1"></i> Split Bill ({{ count($tx['split_items']) }} items)
                            </span>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger fs-6">- Rs. {{ number_format($tx['amount'], 2) }}</div>
                            <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 0.65rem;">EXPENSE</span>
                        </div>
                    </div>
                    <div class="text-muted small mb-2" style="font-size: 0.72rem;">
                        {{ $tx['date']->format('M d, Y') }} &bull; {{ $tx['payment_method'] }}
                    </div>

                    <!-- Breakdown items -->
                    <div class="d-flex flex-column gap-1 mb-1">
                        @foreach($tx['split_items'] as $sItem)
                            <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 bg-light border small" style="font-size: 0.75rem;">
                                <div class="d-flex align-items-center gap-1 text-truncate me-2">
                                    <span class="badge rounded-pill border py-0 px-1" style="background-color: {{ $sItem['color'] ?? '#4f46e5' }}15; color: {{ $sItem['color'] ?? '#4f46e5' }}; font-size: 0.66rem;">
                                        <i class="bi {{ $sItem['icon'] ?? 'bi-tag' }} me-1"></i>{{ $sItem['category_name'] }}
                                    </span>
                                    <span class="text-dark small text-truncate">{{ $sItem['note'] ?: 'Item' }}</span>
                                </div>
                                <span class="fw-bold text-danger small text-nowrap">Rs. {{ number_format($sItem['amount'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end pt-2 border-top mt-2">
                        <a href="{{ route('expenses.edit', $tx['id']) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-secondary">
                            <i class="bi bi-pencil"></i> Edit Split Item
                        </a>
                    </div>
                </div>
            @else
                <div class="card card-custom p-3 mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card-icon" style="width: 34px; height: 34px; background-color: {{ $tx['color'] }}20; color: {{ $tx['color'] }};">
                                <i class="bi {{ $tx['icon'] }} fs-6"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">{{ $tx['category'] }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $tx['date']->format('M d, Y') }} &bull; {{ $tx['payment_method'] }}</small>
                                @if(!empty($tx['transport_mode']))
                                    <div class="mt-1">
                                        @if($tx['transport_mode'] === 'PickMe')
                                            <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.66rem; font-weight: 600;">
                                                <i class="bi bi-geo-alt-fill text-dark me-1"></i>PickMe{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                            </span>
                                        @elseif($tx['transport_mode'] === 'Uber')
                                            <span class="badge bg-dark text-white" style="font-size: 0.66rem; font-weight: 600;">
                                                <i class="bi bi-car-front-fill text-light me-1"></i>Uber{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                            </span>
                                        @elseif($tx['transport_mode'] === 'Bus')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.66rem; font-weight: 600;">
                                                <i class="bi bi-bus-front-fill me-1"></i>Bus
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.66rem; font-weight: 600;">
                                                {{ $tx['transport_mode'] }}{{ !empty($tx['vehicle_type']) ? ' • ' . $tx['vehicle_type'] : '' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold @if($tx['type'] == 'income') text-success @elseif($tx['type'] == 'expense') text-danger @else {{ ($tx['settlement_direction'] ?? '') == 'inflow' ? 'text-info' : 'text-warning' }} @endif">
                                {{ ($tx['type'] == 'income' || ($tx['settlement_direction'] ?? '') == 'inflow') ? '+' : '-' }} Rs. {{ number_format($tx['amount'], 2) }}
                            </div>
                            <span class="badge @if($tx['type'] == 'income') bg-success-subtle text-success @elseif($tx['type'] == 'expense') bg-danger-subtle text-danger @else {{ ($tx['settlement_direction'] ?? '') == 'inflow' ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis' }} @endif rounded-pill" style="font-size: 0.65rem;">
                                {{ strtoupper($tx['type'] == 'settlement' ? (($tx['settlement_direction'] ?? '') == 'inflow' ? 'SETTLED' : 'DEBT PAID') : $tx['type']) }}
                            </span>
                        </div>
                    </div>
                    @if($tx['note'])
                        <div class="bg-light p-2 rounded-2 small text-muted mt-2" style="font-size: 0.78rem;">
                            {{ $tx['note'] }}
                        </div>
                    @endif
                    <div class="d-flex justify-content-end pt-2 border-top mt-2">
                        @if($tx['type'] === 'expense')
                            <a href="{{ route('expenses.edit', $tx['id']) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-secondary">
                                <i class="bi bi-pencil"></i> Edit Expense
                            </a>
                        @elseif($tx['type'] === 'income')
                            <a href="{{ route('incomes.edit', $tx['id']) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-secondary">
                                <i class="bi bi-pencil"></i> Edit Income
                            </a>
                        @elseif($tx['type'] === 'settlement')
                            <a href="{{ route('loans.index') }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-secondary">
                                <i class="bi bi-arrow-right"></i> View Loan
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <div class="text-muted mb-3"><i class="bi bi-inbox display-5"></i></div>
        <h5 class="fw-bold">No Transactions Found</h5>
        <p class="text-muted small mb-0">Try changing your filters or date range.</p>
    </div>
@endif
@endsection
