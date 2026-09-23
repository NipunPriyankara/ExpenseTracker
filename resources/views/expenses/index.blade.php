@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-danger me-2"></i>Expense Management</h4>
        <p class="text-muted small mb-0">Record, search, and manage all your personal expenses.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#quickAddModal">
            <i class="bi bi-plus-lg"></i>
            <span>Add Expense</span>
        </button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted">Search Note/Travel</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-6 col-sm-6 col-lg-2">
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

        <div class="col-6 col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted">Travel Mode</label>
            <select name="transport_mode" class="form-select form-select-sm">
                <option value="">All Travel</option>
                <option value="Bus" {{ request('transport_mode') == 'Bus' ? 'selected' : '' }}>🚌 Bus (බස්)</option>
                <option value="PickMe" {{ request('transport_mode') == 'PickMe' ? 'selected' : '' }}>🚗 PickMe</option>
                <option value="Uber" {{ request('transport_mode') == 'Uber' ? 'selected' : '' }}>🚙 Uber</option>
                <option value="Train" {{ request('transport_mode') == 'Train' ? 'selected' : '' }}>🚆 Train</option>
                <option value="Other" {{ request('transport_mode') == 'Other' ? 'selected' : '' }}>➕ Other</option>
            </select>
        </div>

        <div class="col-6 col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted">Payment</label>
            <select name="payment_method" class="form-select form-select-sm">
                <option value="">All Methods</option>
                <option value="Cash" {{ request('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                <option value="Bank" {{ request('payment_method') == 'Bank' ? 'selected' : '' }}>Bank</option>
                <option value="Debit Card" {{ request('payment_method') == 'Debit Card' ? 'selected' : '' }}>Debit Card</option>
                <option value="Credit Card" {{ request('payment_method') == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                <option value="Online Payment" {{ request('payment_method') == 'Online Payment' ? 'selected' : '' }}>Online Payment</option>
                <option value="Other" {{ request('payment_method') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="col-6 col-sm-8 col-lg-3">
            <label class="form-label small fw-semibold text-muted">Date Range</label>
            <div class="input-group input-group-sm">
                <input type="date" name="start_date" class="form-control px-2" value="{{ request('start_date') }}" title="From Date">
                <span class="input-group-text bg-light px-1">-</span>
                <input type="date" name="end_date" class="form-control px-2" value="{{ request('end_date') }}" title="To Date">
            </div>
        </div>

        <div class="col-6 col-sm-4 col-lg-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill rounded-3" title="Apply Filter">
                <i class="bi bi-funnel"></i>
            </button>
            @if(request()->hasAny(['search', 'category_id', 'payment_method', 'transport_mode', 'start_date', 'end_date', 'sort']))
                <a href="{{ route('expenses.index') }}" class="btn btn-light btn-sm rounded-3 border px-2 d-flex align-items-center justify-content-center" title="Reset Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Summary Ribbon -->
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div class="small text-muted">
        Showing <strong>{{ $expenses->total() }}</strong> expenses recorded
    </div>
    <div class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-6 fw-bold">
        Filtered Total: Rs. {{ number_format($totalFilteredAmount, 2) }}
    </div>
</div>

@if($expenses->count() > 0)
    <!-- Desktop Table View (Hidden on mobile) -->
    <div class="card card-custom d-none d-md-block overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th class="ps-3 py-3">DATE</th>
                        <th>CATEGORY</th>
                        <th>NOTE</th>
                        <th>PAYMENT METHOD</th>
                        <th>RECEIPT</th>
                        <th class="text-end">AMOUNT</th>
                        <th class="text-center pe-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        @php
                            $isSplit = $expense->isSplit() && $expense->splitGroupItems->count() > 1;
                            $splitTotal = $isSplit ? $expense->splitGroupItems->sum('amount') : $expense->amount;
                            $itemsCount = $isSplit ? $expense->splitGroupItems->count() : 1;
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium text-dark">
                                {{ $expense->date->format('M d, Y') }}
                            </td>
                            <td>
                                @if($isSplit)
                                    <span class="badge rounded-pill px-2 py-1 text-white shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 0.74rem;">
                                        <i class="bi bi-receipt-cutoff"></i> Split Bill ({{ $itemsCount }} items)
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $expense->category->color ?? '#64748b' }}15; color: {{ $expense->category->color ?? '#64748b' }}; border: 1px solid {{ $expense->category->color ?? '#64748b' }}30;">
                                        <i class="bi {{ $expense->category->icon ?? 'bi-tag' }} me-1"></i>
                                        {{ $expense->category->display_name ?? 'Uncategorized' }}
                                    </span>
                                @endif

                                @if(!$isSplit && $expense->transport_mode)
                                    <div class="mt-1">
                                        @if($expense->transport_mode === 'PickMe')
                                            <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.7rem; font-weight: 600;">
                                                <i class="bi bi-geo-alt-fill text-dark me-1"></i>PickMe{{ $expense->vehicle_type ? ' • ' . $expense->vehicle_type : '' }}
                                            </span>
                                        @elseif($expense->transport_mode === 'Uber')
                                            <span class="badge bg-dark text-white border" style="font-size: 0.7rem; font-weight: 600;">
                                                <i class="bi bi-car-front-fill text-light me-1"></i>Uber{{ $expense->vehicle_type ? ' • ' . $expense->vehicle_type : '' }}
                                            </span>
                                        @elseif($expense->transport_mode === 'Bus')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem; font-weight: 600;">
                                                <i class="bi bi-bus-front-fill me-1"></i>Bus
                                            </span>
                                        @elseif($expense->transport_mode === 'Train')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.7rem; font-weight: 600;">
                                                <i class="bi bi-train-front-fill me-1"></i>Train
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.7rem; font-weight: 600;">
                                                <i class="bi bi-compass-fill me-1"></i>{{ $expense->transport_mode }}{{ $expense->vehicle_type ? ' • ' . $expense->vehicle_type : '' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($isSplit)
                                    <div class="d-flex flex-column gap-1 my-1" style="min-width: 280px; max-width: 480px;">
                                        @foreach($expense->splitGroupItems as $item)
                                            <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 bg-light border small" style="font-size: 0.77rem;">
                                                <div class="d-flex align-items-center gap-1 text-truncate me-2">
                                                    <span class="badge rounded-pill border py-0 px-2" style="background-color: {{ $item->category->color ?? '#4f46e5' }}15; color: {{ $item->category->color ?? '#4f46e5' }}; font-size: 0.68rem; font-weight: 600;">
                                                        <i class="bi {{ $item->category->icon ?? 'bi-tag' }} me-1"></i>{{ $item->category->display_name }}
                                                    </span>
                                                    <span class="text-dark fw-medium text-truncate">{{ $item->note ?: 'Item' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-danger text-nowrap">Rs. {{ number_format($item->amount, 2) }}</span>
                                                    <a href="{{ route('expenses.edit', $item->id) }}" class="btn btn-sm btn-light border py-0 px-1 rounded text-primary" title="Edit this item" style="font-size: 0.72rem;">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">{{ $expense->note ?: '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-2 px-2 py-1 small">
                                    {{ $expense->payment_method }}
                                </span>
                            </td>
                            <td>
                                @if($expense->receipt_path)
                                    @php $isPdf = str_ends_with(strtolower($expense->receipt_path), '.pdf'); @endphp
                                    <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-primary">
                                        <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf text-danger' : 'bi-image' }} me-1"></i> View
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-danger">
                                Rs. {{ number_format($splitTotal, 2) }}
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-outline-secondary rounded-start-2" title="{{ $isSplit ? 'Edit Split Item' : 'Edit' }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger rounded-end-2" title="{{ $isSplit ? 'Delete Split Bill' : 'Delete' }}" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="document.getElementById('deleteExpenseForm').action='{{ route('expenses.destroy', $expense->id) }}'; document.getElementById('deleteModalDesc').textContent = '{{ $isSplit ? 'This action will remove all ' . $itemsCount . ' items in this split bill.' : 'This action will remove the record from your active expenses.' }}';">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View (Visible on mobile only) -->
    <div class="d-md-none mb-4">
        @foreach($expenses as $expense)
            @php
                $isSplit = $expense->isSplit() && $expense->splitGroupItems->count() > 1;
                $splitTotal = $isSplit ? $expense->splitGroupItems->sum('amount') : $expense->amount;
                $itemsCount = $isSplit ? $expense->splitGroupItems->count() : 1;
            @endphp
            @if($isSplit)
                <!-- Split Bill Single Mobile Card -->
                <div class="card card-custom p-3 mb-2 border-start border-4" style="border-left-color: #4f46e5 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-2 py-1 text-white shadow-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 0.72rem;">
                                <i class="bi bi-receipt-cutoff me-1"></i> Split Bill ({{ $itemsCount }} items)
                            </span>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger fs-6">Rs. {{ number_format($splitTotal, 2) }}</div>
                        </div>
                    </div>
                    <div class="text-muted small mb-2" style="font-size: 0.72rem;">
                        {{ $expense->date->format('M d, Y') }} &bull; {{ $expense->payment_method }}
                    </div>

                    <!-- Breakdown items -->
                    <div class="d-flex flex-column gap-1 mb-2">
                        @foreach($expense->splitGroupItems as $item)
                            <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 bg-light border small" style="font-size: 0.75rem;">
                                <div class="d-flex align-items-center gap-1 text-truncate me-2">
                                    <span class="badge rounded-pill border py-0 px-1" style="background-color: {{ $item->category->color ?? '#4f46e5' }}15; color: {{ $item->category->color ?? '#4f46e5' }}; font-size: 0.66rem;">
                                        <i class="bi {{ $item->category->icon ?? 'bi-tag' }} me-1"></i>{{ $item->category->display_name }}
                                    </span>
                                    <span class="text-dark small text-truncate">{{ $item->note ?: 'Item' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-bold text-danger small text-nowrap">Rs. {{ number_format($item->amount, 2) }}</span>
                                    <a href="{{ route('expenses.edit', $item->id) }}" class="btn btn-sm btn-link p-0 text-primary ms-1" title="Edit this item">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-1 border-top">
                        <div>
                            @if($expense->receipt_path)
                                <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="small text-primary text-decoration-none">
                                    <i class="bi bi-paperclip"></i> Receipt
                                </a>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-secondary" title="Edit Split Item">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="document.getElementById('deleteExpenseForm').action='{{ route('expenses.destroy', $expense->id) }}'; document.getElementById('deleteModalDesc').textContent = 'This action will remove all {{ $itemsCount }} items in this split bill.';">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- Single Expense Mobile Card -->
                <div class="card card-custom p-3 mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card-icon" style="width: 36px; height: 36px; background-color: {{ $expense->category->color ?? '#64748b' }}20; color: {{ $expense->category->color ?? '#64748b' }};">
                                <i class="bi {{ $expense->category->icon ?? 'bi-tag' }} fs-6"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">{{ $expense->category->display_name ?? 'Expense' }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $expense->date->format('M d, Y') }} &bull; {{ $expense->payment_method }}</small>
                                @if($expense->transport_mode)
                                    <div class="mt-1">
                                        @if($expense->transport_mode === 'PickMe')
                                            <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-geo-alt-fill text-dark me-1"></i>PickMe{{ $expense->vehicle_type ? ' • ' . $expense->vehicle_type : '' }}
                                            </span>
                                        @elseif($expense->transport_mode === 'Uber')
                                            <span class="badge bg-dark text-white" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-car-front-fill text-light me-1"></i>Uber{{ $expense->vehicle_type ? ' • ' . $expense->vehicle_type : '' }}
                                            </span>
                                        @elseif($expense->transport_mode === 'Bus')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-bus-front-fill me-1"></i>Bus
                                            </span>
                                        @elseif($expense->transport_mode === 'Train')
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-train-front-fill me-1"></i>Train
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.68rem; font-weight: 600;">
                                                <i class="bi bi-compass-fill me-1"></i>{{ $expense->transport_mode }}{{ $expense->vehicle_type ? ' • ' . $expense->vehicle_type : '' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger">Rs. {{ number_format($expense->amount, 2) }}</div>
                        </div>
                    </div>

                    @if($expense->note)
                        <div class="bg-light p-2 rounded-2 small text-muted mb-2" style="font-size: 0.8rem;">
                            {{ $expense->note }}
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-between pt-1 border-top">
                        <div>
                            @if($expense->receipt_path)
                                <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="small text-primary text-decoration-none">
                                    <i class="bi bi-paperclip"></i> Receipt
                                </a>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small">
                                <i class="bi bi-pencil text-secondary"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="document.getElementById('deleteExpenseForm').action='{{ route('expenses.destroy', $expense->id) }}';">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $expenses->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <div class="text-muted mb-3"><i class="bi bi-search display-5"></i></div>
        <h5 class="fw-bold">No Expenses Found</h5>
        <p class="text-muted small mb-3">No expenses match your search or filter criteria.</p>
        <div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                <i class="bi bi-plus-lg me-1"></i> Add First Expense
            </button>
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill display-5"></i>
                </div>
                <h6 class="fw-bold mb-1">Delete Expense?</h6>
                <p class="text-muted small mb-3">This action will remove the record from your active expenses.</p>
                <form id="deleteExpenseForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-3 w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-3 w-50">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
