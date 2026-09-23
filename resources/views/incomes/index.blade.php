@extends('layouts.app')

@section('title', 'Income')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-wallet2 text-success me-2"></i>Income Management</h4>
        <p class="text-muted small mb-0">Record, track, and categorize all your earnings, salary, and revenue.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-success rounded-pill px-3 shadow-sm d-flex align-items-center gap-1 text-white" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
            <i class="bi bi-plus-lg"></i>
            <span>Record Income</span>
        </button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="{{ route('incomes.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label small fw-semibold text-muted">Search Source/Note/Category</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="e.g. Salary, Client X, Freelance..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label small fw-semibold text-muted">Category</label>
            <select name="category_id" class="form-select form-select-sm">
                <option value="">All Income Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->display_name }}
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

        <div class="col-6 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-success text-white btn-sm flex-grow-1 rounded-3" title="Apply Filter">
                <i class="bi bi-funnel"></i>
            </button>
            @if(request()->hasAny(['search', 'category_id', 'source', 'start_date', 'end_date', 'sort']))
                <a href="{{ route('incomes.index') }}" class="btn btn-light btn-sm rounded-3 border" title="Reset Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Summary Ribbon -->
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div class="small text-muted">
        Showing <strong>{{ $incomes->total() }}</strong> income entries
    </div>
    <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-6 fw-bold">
        Filtered Total: Rs. {{ number_format($totalFilteredAmount, 2) }}
    </div>
</div>

@if($incomes->count() > 0)
    <!-- Desktop Table View -->
    <div class="card card-custom d-none d-md-block overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th class="ps-3 py-3">DATE</th>
                        <th>CATEGORY</th>
                        <th>DESCRIPTION / BREAKDOWN</th>
                        <th class="text-end">AMOUNT</th>
                        <th class="text-center pe-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incomes as $income)
                        @php
                            $isSplit = $income->isSplit() && $income->splitGroupItems->count() > 1;
                            $splitTotal = $isSplit ? $income->splitGroupItems->sum('amount') : $income->amount;
                            $itemsCount = $isSplit ? $income->splitGroupItems->count() : 1;
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium text-dark">
                                {{ $income->date->format('M d, Y') }}
                            </td>
                            <td>
                                @if($isSplit)
                                    <span class="badge rounded-pill px-2 py-1 text-white shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-size: 0.74rem;">
                                        <i class="bi bi-receipt-cutoff"></i> Split Income ({{ $itemsCount }} items)
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $income->category->color ?? '#10b981' }}15; color: {{ $income->category->color ?? '#10b981' }}; border: 1px solid {{ $income->category->color ?? '#10b981' }}30;">
                                        <i class="bi {{ $income->category->icon ?? 'bi-cash-coin' }} me-1"></i>
                                        {{ $income->category->display_name ?? $income->source }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isSplit)
                                    <div class="d-flex flex-column gap-1 my-1" style="min-width: 280px; max-width: 480px;">
                                        @foreach($income->splitGroupItems as $item)
                                            <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded-2 bg-light border small" style="font-size: 0.77rem;">
                                                <div class="d-flex align-items-center gap-1 text-truncate me-2">
                                                    <span class="badge rounded-pill border py-0 px-2" style="background-color: {{ $item->category->color ?? '#10b981' }}15; color: {{ $item->category->color ?? '#10b981' }}; font-size: 0.68rem; font-weight: 600;">
                                                        <i class="bi {{ $item->category->icon ?? 'bi-cash-coin' }} me-1"></i>{{ $item->category ? $item->category->display_name : $item->source }}
                                                    </span>
                                                    <span class="text-dark fw-medium text-truncate">{{ $item->note ?: 'Income Item' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-success text-nowrap">+ Rs. {{ number_format($item->amount, 2) }}</span>
                                                    <a href="{{ route('incomes.edit', $item->id) }}" class="btn btn-sm btn-light border py-0 px-1 rounded text-primary" title="Edit this item" style="font-size: 0.72rem;">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">{{ $income->note ?: '-' }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success">
                                + Rs. {{ number_format($splitTotal, 2) }}
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('incomes.edit', $income->id) }}" class="btn btn-outline-secondary rounded-start-2" title="{{ $isSplit ? 'Edit Split Item' : 'Edit' }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger rounded-end-2" title="{{ $isSplit ? 'Delete Split Income' : 'Delete' }}" data-bs-toggle="modal" data-bs-target="#deleteIncomeModal" onclick="document.getElementById('deleteIncomeForm').action='{{ route('incomes.destroy', $income->id) }}'; document.getElementById('deleteIncomeModalDesc').textContent = '{{ $isSplit ? 'This action will remove all ' . $itemsCount . ' items in this split income deposit.' : 'This record will be removed from your earnings.' }}';">
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

    <!-- Mobile Card View -->
    <div class="d-md-none mb-4">
        @foreach($incomes as $income)
            @php
                $isSplit = $income->isSplit() && $income->splitGroupItems->count() > 1;
                $splitTotal = $isSplit ? $income->splitGroupItems->sum('amount') : $income->amount;
                $itemsCount = $isSplit ? $income->splitGroupItems->count() : 1;
            @endphp

            @if($isSplit)
                <div class="card card-custom p-3 mb-2 border-start border-4" style="border-left-color: #10b981 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-2 py-1 text-white shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-size: 0.72rem;">
                                <i class="bi bi-receipt-cutoff me-1"></i> Split Income ({{ $itemsCount }} items)
                            </span>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">+ Rs. {{ number_format($splitTotal, 2) }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $income->date->format('M d, Y') }}</small>
                        </div>
                    </div>

                    <div class="bg-light p-2 rounded-2 mb-2 d-flex flex-column gap-1">
                        @foreach($income->splitGroupItems as $item)
                            <div class="d-flex justify-content-between align-items-center small" style="font-size: 0.76rem;">
                                <div class="text-truncate me-2">
                                    <span class="fw-semibold text-dark">{{ $item->category ? $item->category->display_name : $item->source }}:</span>
                                    <span class="text-muted">{{ $item->note ?: 'Item' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-bold text-success text-nowrap">+ Rs. {{ number_format($item->amount, 2) }}</span>
                                    <a href="{{ route('incomes.edit', $item->id) }}" class="btn btn-sm btn-light border py-0 px-1 rounded text-primary" style="font-size: 0.68rem;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex align-items-center justify-content-end pt-1 border-top gap-2">
                        <a href="{{ route('incomes.edit', $income->id) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small">
                            <i class="bi bi-pencil text-secondary"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-danger" data-bs-toggle="modal" data-bs-target="#deleteIncomeModal" onclick="document.getElementById('deleteIncomeForm').action='{{ route('incomes.destroy', $income->id) }}'; document.getElementById('deleteIncomeModalDesc').textContent = 'This will remove all {{ $itemsCount }} items in this split deposit.';">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
            @else
                <div class="card card-custom p-3 mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-card-icon" style="width: 36px; height: 36px; background-color: {{ $income->category->color ?? '#10b981' }}20; color: {{ $income->category->color ?? '#10b981' }};">
                                <i class="bi {{ $income->category->icon ?? 'bi-cash-coin' }} fs-6"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">{{ $income->category->display_name ?? $income->source }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $income->date->format('M d, Y') }}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">+ Rs. {{ number_format($income->amount, 2) }}</div>
                        </div>
                    </div>

                    @if($income->note)
                        <div class="bg-light p-2 rounded-2 small text-muted mb-2" style="font-size: 0.8rem;">
                            {{ $income->note }}
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-end pt-1 border-top gap-2">
                        <a href="{{ route('incomes.edit', $income->id) }}" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small">
                            <i class="bi bi-pencil text-secondary"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-2 small text-danger" data-bs-toggle="modal" data-bs-target="#deleteIncomeModal" onclick="document.getElementById('deleteIncomeForm').action='{{ route('incomes.destroy', $income->id) }}'; document.getElementById('deleteIncomeModalDesc').textContent = 'This record will be removed from your earnings.';">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $incomes->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="card card-custom p-5 text-center my-3">
        <div class="text-muted mb-3"><i class="bi bi-wallet2 display-5 text-success"></i></div>
        <h5 class="fw-bold">No Income Recorded Yet</h5>
        <p class="text-muted small mb-3">Add your salary, freelance earnings, investments, or multiple income streams.</p>
        <div>
            <button class="btn btn-success text-white rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="bi bi-plus-lg me-1"></i> Record First Income
            </button>
        </div>
    </div>
@endif

<!-- Add Income Modal (Supports Single & Multiple / Itemized Incomes) -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-badge bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="addIncomeModalLabel">Record Income</h5>
                        <p class="text-muted small mb-0">Record single or itemized income streams</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            @php
                $incomeCatData = [];
                foreach($categories as $c) {
                    $incomeCatData[] = [
                        'id' => $c->id,
                        'name' => $c->name,
                        'display_name' => $c->display_name,
                        'parent_id' => $c->parent_id,
                    ];
                }
                $incomeCategoriesJson = json_encode($incomeCatData);
            @endphp

            <form method="POST" action="{{ route('incomes.store') }}" id="incomeForm">
                @csrf
                <div class="modal-body pt-3">

                    <!-- Split / Itemized Income Toggle Banner -->
                    <div class="d-flex align-items-center justify-content-between p-2 px-3 mb-3 rounded-3 border" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #86efac !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-success fs-5"></i>
                            <div>
                                <span class="fw-bold small text-dark d-block">Multiple / Itemized Income (එකවර ආදායම් අයිතම කිහිපයක් දැමීම)</span>
                                <span class="text-muted" style="font-size: 0.73rem;">Record multiple income streams (e.g. Basic Salary + Bonus, Client A + Client B) in one deposit</span>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="incomeSplitToggle" name="is_split" value="1" style="cursor: pointer; transform: scale(1.2);">
                        </div>
                    </div>

                    <!-- ================= INLINE INCOME CATEGORY CREATOR ================= -->
                    <div id="inlineIncomeCatCard" class="card border border-success p-3 mb-3 rounded-3 shadow-sm bg-light-subtle d-none" style="border-left: 4px solid #10b981 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                            <span class="fw-bold small text-success d-flex align-items-center gap-1">
                                <i class="bi bi-tags-fill"></i> New Income Category / Sub-category (අලුත් ආදායම් කාණ්ඩයක් සාදන්න)
                            </span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" id="closeIncomeInlineCatBtn" title="Close">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div id="incomeInlineCatAlert" class="alert alert-danger py-1 px-2 small d-none mb-2"></div>

                        <div class="row g-2">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-semibold text-muted mb-1">Name *</label>
                                <input type="text" id="inlineIncomeCatName" class="form-control form-control-sm" placeholder="e.g. YouTube Earnings, Crypto, Dividend...">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Parent Category (උප කාණ්ඩයක් කිරීමට)</label>
                                <select id="inlineIncomeCatParent" class="form-select form-select-sm">
                                    <option value="">-- None (Create as Main Category) --</option>
                                    @foreach($categories->whereNull('parent_id') as $pCat)
                                        <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text small" style="font-size: 0.68rem;">උදා: "Salary & Wages" තේරුවහොත් එය යටතේ උප කාණ්ඩයක් ලෙස හැදේ.</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Badge Color</label>
                                <input type="color" id="inlineIncomeCatColor" class="form-control form-control-color form-control-sm w-100" value="#10b981" title="Choose color">
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <button type="button" class="btn btn-light btn-sm rounded-pill px-3" id="cancelIncomeInlineCatBtn">Cancel</button>
                                <button type="button" class="btn btn-success text-white btn-sm rounded-pill px-4 shadow-sm" id="saveIncomeInlineCatBtn">
                                    <i class="bi bi-check2 me-1"></i> Save & Use Category
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ================= SINGLE INCOME MODE ================= -->
                    <div id="incomeSingleFields">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Amount (Rs.) *</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold text-success border-end-0">Rs.</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="singleIncomeAmount" class="form-control fw-bold fs-4 border-start-0" placeholder="0.00" required autofocus>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold text-muted mb-0">Category & Subcategory *</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-success trigger-income-inline-cat-btn" data-target-select="singleIncomeCategory" style="font-size: 0.74rem;">
                                        <i class="bi bi-plus-circle me-1"></i>New Category / Sub-category
                                    </button>
                                </div>
                                <select name="category_id" id="singleIncomeCategory" class="form-select income-category-select" required>
                                    <option value="" disabled selected>Select income category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Date *</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted">Description / Note (Optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="e.g. September Salary, Client project payment">
                        </div>
                    </div>

                    <!-- ================= MULTIPLE / ITEMISED INCOME MODE ================= -->
                    <div id="incomeSplitFields" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <div>
                                <span class="fw-bold text-dark small"><i class="bi bi-list-check text-success me-1"></i>Itemized Income Streams</span>
                                <span class="text-muted ms-2 small" id="incomeSplitItemCountText">(0 items)</span>
                            </div>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-success trigger-income-inline-cat-btn" style="font-size: 0.74rem;">
                                <i class="bi bi-plus-circle me-1"></i>New Category / Sub-category
                            </button>
                        </div>

                        <!-- Dynamic Items List -->
                        <div id="incomeSplitItemsList" class="d-flex flex-column gap-2 mb-3">
                            <!-- Items rows inserted dynamically via JS -->
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light mb-3">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" id="incomeAddSplitItemBtn">
                                <i class="bi bi-plus-lg me-1"></i> Add Another Stream (තවත් ආදායමක්)
                            </button>
                            <div class="text-end">
                                <span class="text-muted small me-2">Calculated Total:</span>
                                <span class="fw-bold fs-5 text-success" id="incomeSplitCalculatedTotal">Rs. 0.00</span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted">Deposit Date *</label>
                            <input type="date" name="date" id="splitIncomeDate" class="form-control" value="{{ date('Y-m-d') }}" disabled>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white rounded-3 px-4 shadow-sm" id="incomeSaveBtn">
                        <i class="bi bi-check2 me-1"></i> Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteIncomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill display-5"></i>
                </div>
                <h6 class="fw-bold mb-1">Delete Income Entry?</h6>
                <p class="text-muted small mb-3" id="deleteIncomeModalDesc">This record will be removed from your earnings.</p>
                <form id="deleteIncomeForm" method="POST" action="">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const splitToggle = document.getElementById('incomeSplitToggle');
    const singleSection = document.getElementById('incomeSingleFields');
    const splitSection = document.getElementById('incomeSplitFields');
    const singleAmount = document.getElementById('singleIncomeAmount');
    const singleCategory = document.getElementById('singleIncomeCategory');
    const splitDate = document.getElementById('splitIncomeDate');
    const splitList = document.getElementById('incomeSplitItemsList');
    const addSplitItemBtn = document.getElementById('incomeAddSplitItemBtn');
    const splitTotalEl = document.getElementById('incomeSplitCalculatedTotal');
    const itemCountText = document.getElementById('incomeSplitItemCountText');
    const saveSubmitBtn = document.getElementById('incomeSaveBtn');

    let availableIncomeCategories = {!! $incomeCategoriesJson ?? '[]' !!};
    let splitItemIndex = 0;
    let currentTargetSelect = null;

    function buildIncomeCatOptionsHtml(selectedId = '') {
        let html = '<option value="" disabled ' + (!selectedId ? 'selected' : '') + '>Select category</option>';
        availableIncomeCategories.forEach(cat => {
            const isSel = (cat.id == selectedId) ? 'selected' : '';
            html += `<option value="${cat.id}" ${isSel}>${cat.display_name}</option>`;
        });
        return html;
    }

    function addIncomeSplitRow(defaultNote = '', defaultAmount = '', defaultCatId = '') {
        if (!splitList) return;
        const idx = splitItemIndex++;

        const rowDiv = document.createElement('div');
        rowDiv.className = 'card p-2 border bg-light-subtle rounded-3 shadow-none split-income-row mb-2';
        rowDiv.id = `income_split_row_${idx}`;
        rowDiv.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <input type="text" name="items[${idx}][note]" class="form-control form-control-sm" placeholder="Description (e.g. Basic Salary, OT, Bonus)" value="${defaultNote}">
                </div>
                <div class="col-6 col-md-4">
                    <div class="input-group input-group-sm">
                        <select name="items[${idx}][category_id]" id="inc_split_cat_${idx}" class="form-select form-select-sm income-category-select" required>
                            ${buildIncomeCatOptionsHtml(defaultCatId)}
                        </select>
                        <button type="button" class="btn btn-outline-success btn-sm px-2 row-income-cat-btn" data-target-id="inc_split_cat_${idx}" title="Create subcategory">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="col-5 col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light fw-bold text-success px-1">Rs.</span>
                        <input type="number" step="0.01" min="0.01" name="items[${idx}][amount]" class="form-control form-control-sm fw-bold inc-split-amount-input" placeholder="0.00" value="${defaultAmount}" required>
                    </div>
                </div>
                <div class="col-1 col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle remove-inc-row-btn" title="Remove item" style="width: 28px; height: 28px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        splitList.appendChild(rowDiv);
        bindIncomeSplitRowEvents(rowDiv);
        recalcIncomeSplitTotal();
    }

    function bindIncomeSplitRowEvents(rowDiv) {
        const removeBtn = rowDiv.querySelector('.remove-inc-row-btn');
        const amountInput = rowDiv.querySelector('.inc-split-amount-input');
        const rowCatBtn = rowDiv.querySelector('.row-income-cat-btn');

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (splitList.querySelectorAll('.split-income-row').length <= 1) {
                    alert('Itemized income entry must contain at least 1 item.');
                    return;
                }
                rowDiv.remove();
                recalcIncomeSplitTotal();
            });
        }

        if (amountInput) {
            amountInput.addEventListener('input', recalcIncomeSplitTotal);
        }

        if (rowCatBtn) {
            rowCatBtn.addEventListener('click', function() {
                const targetId = rowCatBtn.getAttribute('data-target-id');
                currentTargetSelect = document.getElementById(targetId);
                openIncomeInlineCatCard();
            });
        }
    }

    function recalcIncomeSplitTotal() {
        if (!splitList || !splitTotalEl) return;
        const rows = splitList.querySelectorAll('.split-income-row');
        let total = 0;
        rows.forEach(r => {
            const amtInput = r.querySelector('.inc-split-amount-input');
            if (amtInput) {
                const val = parseFloat(amtInput.value) || 0;
                total += val;
            }
        });
        splitTotalEl.textContent = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (itemCountText) {
            itemCountText.textContent = `(${rows.length} ${rows.length === 1 ? 'item' : 'items'})`;
        }
    }

    function toggleIncomeSplitMode() {
        if (!splitToggle || !singleSection || !splitSection) return;
        const isSplit = splitToggle.checked;
        if (isSplit) {
            singleSection.classList.add('d-none');
            splitSection.classList.remove('d-none');
            if (singleAmount) {
                singleAmount.removeAttribute('required');
                singleAmount.disabled = true;
            }
            if (singleCategory) {
                singleCategory.removeAttribute('required');
                singleCategory.disabled = true;
            }
            if (splitDate) splitDate.disabled = false;
            splitList.querySelectorAll('input, select').forEach(el => el.disabled = false);
            if (saveSubmitBtn) saveSubmitBtn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Save All Itemized Incomes';

            if (splitList.querySelectorAll('.split-income-row').length === 0) {
                // Find default categories if available
                let basicSalId = '';
                let bonusId = '';
                availableIncomeCategories.forEach(c => {
                    const low = c.name.toLowerCase();
                    if (low.includes('basic') || low.includes('sal')) basicSalId = basicSalId || c.id;
                    if (low.includes('bonus') || low.includes('ot')) bonusId = bonusId || c.id;
                });
                addIncomeSplitRow('Basic Salary', '', basicSalId);
                addIncomeSplitRow('Bonus / Allowance', '', bonusId);
            }
        } else {
            singleSection.classList.remove('d-none');
            splitSection.classList.add('d-none');
            if (singleAmount) {
                singleAmount.setAttribute('required', 'required');
                singleAmount.disabled = false;
            }
            if (singleCategory) {
                singleCategory.setAttribute('required', 'required');
                singleCategory.disabled = false;
            }
            if (splitDate) splitDate.disabled = true;
            splitList.querySelectorAll('input, select').forEach(el => el.disabled = true);
            if (saveSubmitBtn) saveSubmitBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save Income';
        }
    }

    if (splitToggle) splitToggle.addEventListener('change', toggleIncomeSplitMode);
    if (addSplitItemBtn) {
        addSplitItemBtn.addEventListener('click', function() {
            addIncomeSplitRow('', '');
        });
    }

    // Inline Income Category Creator
    const inlineCard = document.getElementById('inlineIncomeCatCard');
    const inlineName = document.getElementById('inlineIncomeCatName');
    const inlineParent = document.getElementById('inlineIncomeCatParent');
    const inlineColor = document.getElementById('inlineIncomeCatColor');
    const inlineAlert = document.getElementById('incomeInlineCatAlert');
    const saveInlineBtn = document.getElementById('saveIncomeInlineCatBtn');
    const closeInlineBtn = document.getElementById('closeIncomeInlineCatBtn');
    const cancelInlineBtn = document.getElementById('cancelIncomeInlineCatBtn');

    function openIncomeInlineCatCard() {
        if (!inlineCard) return;
        inlineCard.classList.remove('d-none');
        if (inlineAlert) inlineAlert.classList.add('d-none');
        if (inlineName) {
            inlineName.value = '';
            inlineName.focus();
        }
        inlineCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function closeIncomeInlineCatCard() {
        if (!inlineCard) return;
        inlineCard.classList.add('d-none');
        if (inlineAlert) inlineAlert.classList.add('d-none');
    }

    document.querySelectorAll('.trigger-income-inline-cat-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target-select');
            currentTargetSelect = targetId ? document.getElementById(targetId) : null;
            openIncomeInlineCatCard();
        });
    });

    if (closeInlineBtn) closeInlineBtn.addEventListener('click', closeIncomeInlineCatCard);
    if (cancelInlineBtn) cancelInlineBtn.addEventListener('click', closeIncomeInlineCatCard);

    if (saveInlineBtn) {
        saveInlineBtn.addEventListener('click', function() {
            const name = inlineName ? inlineName.value.trim() : '';
            if (!name) {
                if (inlineAlert) {
                    inlineAlert.textContent = 'Please enter a category or sub-category name.';
                    inlineAlert.classList.remove('d-none');
                }
                if (inlineName) inlineName.focus();
                return;
            }

            const parentId = inlineParent ? inlineParent.value : '';
            const color = inlineColor ? inlineColor.value : '#10b981';

            saveInlineBtn.disabled = true;
            saveInlineBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('name', name);
            if (parentId) formData.append('parent_id', parentId);
            formData.append('color', color);
            formData.append('type', 'income');

            fetch("{{ route('categories.quick-store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                saveInlineBtn.disabled = false;
                saveInlineBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save & Use Category';

                if (data.success && data.category) {
                    const newCat = data.category;
                    availableIncomeCategories.push({
                        id: newCat.id,
                        name: newCat.name,
                        display_name: newCat.display_name,
                        parent_id: newCat.parent_id
                    });

                    // Update all category dropdowns in this modal
                    document.querySelectorAll('.income-category-select').forEach(sel => {
                        const opt = document.createElement('option');
                        opt.value = newCat.id;
                        opt.textContent = newCat.display_name;
                        sel.appendChild(opt);
                    });

                    // Update parent dropdown if new was a parent
                    if (!newCat.parent_id && inlineParent) {
                        const pOpt = document.createElement('option');
                        pOpt.value = newCat.id;
                        pOpt.textContent = newCat.name;
                        inlineParent.appendChild(pOpt);
                    }

                    // Pre-select in active target select
                    if (currentTargetSelect) {
                        currentTargetSelect.value = newCat.id;
                    }

                    closeIncomeInlineCatCard();
                } else {
                    if (inlineAlert) {
                        inlineAlert.textContent = data.message || 'Could not save category.';
                        inlineAlert.classList.remove('d-none');
                    }
                }
            })
            .catch(err => {
                saveInlineBtn.disabled = false;
                saveInlineBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save & Use Category';
                if (inlineAlert) {
                    inlineAlert.textContent = 'Server error occurred. Please try again.';
                    inlineAlert.classList.remove('d-none');
                }
            });
        });
    }
});
</script>
@endsection
