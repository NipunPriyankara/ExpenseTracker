@extends('layouts.app')

@section('title', 'Edit Income')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square text-success me-2"></i>Edit Income</h4>
            <a href="{{ route('incomes.index') }}" class="btn btn-light btn-sm border rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Income
            </a>
        </div>

        @if($income->isSplit())
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 p-3" style="background-color: #ecfdf5; border-left: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-success small d-flex align-items-center gap-1">
                        <i class="bi bi-receipt-cutoff"></i> Part of Itemized Income Group
                    </span>
                    <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        {{ $income->splitGroupItems->count() }} items
                    </span>
                </div>
                <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                    You are editing this individual line item. Other items recorded together in this income entry:
                </p>
                <div class="d-flex flex-column gap-1 bg-white p-2 rounded-2 border">
                    @foreach($income->splitGroupItems as $item)
                        <div class="d-flex justify-content-between align-items-center small {{ $item->id === $income->id ? 'fw-bold text-success' : 'text-muted' }}" style="font-size: 0.78rem;">
                            <span>
                                @if($item->id === $income->id)
                                    <i class="bi bi-arrow-right-short text-success"></i>
                                @endif
                                {{ $item->category ? $item->category->display_name : $item->source }}
                                @if($item->note)
                                    <span class="text-muted fst-italic">({{ $item->note }})</span>
                                @endif
                            </span>
                            <span class="fw-semibold">Rs. {{ number_format($item->amount, 2) }}</span>
                        </div>
                    @endforeach
                    <div class="border-top pt-1 mt-1 d-flex justify-content-between align-items-center small fw-bold text-dark" style="font-size: 0.8rem;">
                        <span>Total Deposit:</span>
                        <span class="text-success">Rs. {{ number_format($income->splitGroupItems->sum('amount'), 2) }}</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="card card-custom p-4">
            <form method="POST" action="{{ route('incomes.update', $income->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Amount (Rs.) *</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light fw-bold text-success border-end-0">Rs.</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold fs-4 border-start-0 @error('amount') is-invalid @enderror" value="{{ old('amount', $income->amount) }}" required autofocus>
                    </div>
                    @error('amount')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Category & Subcategory *</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="" disabled>Select income category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $income->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->display_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Date *</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $income->date->format('Y-m-d')) }}" required>
                        @error('date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Description / Note (Optional)</label>
                    <input type="text" name="note" class="form-control" value="{{ old('note', $income->note) }}" placeholder="e.g. September Salary, Client project payout">
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('incomes.index') }}" class="btn btn-light rounded-3 px-4 border flex-grow-1">Cancel</a>
                    <button type="submit" class="btn btn-success text-white rounded-3 px-4 shadow-sm flex-grow-1">
                        <i class="bi bi-check2 me-1"></i> Update Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
