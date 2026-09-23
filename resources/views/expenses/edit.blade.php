@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Expense</h4>
            <a href="{{ route('expenses.index') }}" class="btn btn-light btn-sm border rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Expenses
            </a>
        </div>

        <div class="card card-custom p-4">
            @if($expense->isSplit() && $expense->splitGroupItems->count() > 1)
                <div class="alert alert-primary py-2 px-3 mb-3 rounded-3 small d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                        <div>
                            <strong>Split Bill Item</strong>
                            <div class="text-muted" style="font-size: 0.72rem;">This item is part of a split bill with {{ $expense->splitGroupItems->count() }} items (Total: Rs. {{ number_format($expense->splitGroupItems->sum('amount'), 2) }}).</div>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill">Split</span>
                </div>
            @endif

            <form method="POST" action="{{ route('expenses.update', $expense->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Amount (Rs.) *</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light fw-bold text-danger border-end-0">Rs.</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold fs-4 border-start-0 @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" required autofocus>
                    </div>
                    @error('amount')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-muted">Category *</label>
                        <select name="category_id" id="editExpenseCategory" class="form-select @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-name="{{ strtolower($category->name) }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->display_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-muted">Payment Method *</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            @foreach(['Cash', 'Bank', 'Debit Card', 'Credit Card', 'Online Payment', 'Other'] as $method)
                                <option value="{{ $method }}" {{ old('payment_method', $expense->payment_method) == $method ? 'selected' : '' }}>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $curMode = old('transport_mode', $expense->transport_mode);
                    $curVehicle = old('vehicle_type', $expense->vehicle_type);
                    $hasTravel = !empty($curMode);
                @endphp

                <!-- Travel Details Toggle Link -->
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary" id="editExpenseToggleTravelBtn" style="font-size: 0.78rem;">
                        <i class="bi bi-geo-alt"></i> Travel Details (Bus, PickMe, Uber) <i class="bi {{ $hasTravel ? 'bi-chevron-up' : 'bi-chevron-down' }}" id="editExpenseToggleTravelIcon"></i>
                    </button>
                </div>

                <!-- Travel Details Box -->
                <div id="editExpenseTravelSection" class="p-3 mb-3 rounded-3 border bg-light-subtle {{ $hasTravel ? '' : 'd-none' }}" style="border-left: 4px solid #0ea5e9 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1">
                            <i class="bi bi-compass text-primary"></i> Travel / Transport Details (ගමන් විස්තර)
                        </span>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" id="editExpenseClearTravelBtn" style="font-size: 0.72rem;">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>

                    <!-- Mode selection -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted d-block mb-1">
                            Travel Service / Mode (ගමන් කළ ආකාරය):
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <div>
                                <input type="radio" class="btn-check edit-travel-mode" name="transport_mode" id="edit_mode_bus" value="Bus" {{ $curMode === 'Bus' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" for="edit_mode_bus">
                                    <i class="bi bi-bus-front me-1"></i> Bus (බස්)
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-travel-mode" name="transport_mode" id="edit_mode_pickme" value="PickMe" {{ $curMode === 'PickMe' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 py-1" for="edit_mode_pickme">
                                    <i class="bi bi-geo-alt-fill text-warning me-1"></i> PickMe
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-travel-mode" name="transport_mode" id="edit_mode_uber" value="Uber" {{ $curMode === 'Uber' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1" for="edit_mode_uber">
                                    <i class="bi bi-car-front-fill me-1"></i> Uber
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-travel-mode" name="transport_mode" id="edit_mode_train" value="Train" {{ $curMode === 'Train' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 py-1" for="edit_mode_train">
                                    <i class="bi bi-train-front me-1"></i> Train
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-travel-mode" name="transport_mode" id="edit_mode_other" value="Other" {{ $curMode === 'Other' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="edit_mode_other">
                                    <i class="bi bi-three-dots me-1"></i> Other
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle selection (PickMe or Uber) -->
                    <div id="editExpenseVehicleSection" class="mt-2 pt-2 border-top {{ in_array($curMode, ['PickMe', 'Uber', 'Other']) ? '' : 'd-none' }}">
                        <label class="form-label small fw-semibold text-muted d-block mb-1">
                            Vehicle Type (PickMe / Uber වාහනය):
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <div>
                                <input type="radio" class="btn-check edit-vehicle-type" name="vehicle_type" id="edit_veh_3wheel" value="3-Wheel" {{ $curVehicle === '3-Wheel' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="edit_veh_3wheel">
                                    🛺 3-Wheel (Tuk)
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-vehicle-type" name="vehicle_type" id="edit_veh_bike" value="Bike" {{ $curVehicle === 'Bike' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="edit_veh_bike">
                                    🏍️ Bike (යතුරුපැදි)
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-vehicle-type" name="vehicle_type" id="edit_veh_car" value="Car" {{ $curVehicle === 'Car' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="edit_veh_car">
                                    🚗 Car (කාර්)
                                </label>
                            </div>
                            <div>
                                <input type="radio" class="btn-check edit-vehicle-type" name="vehicle_type" id="edit_veh_van" value="Van" {{ $curVehicle === 'Van' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="edit_veh_van">
                                    🚐 Van (වෑන්)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Date *</label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
                    @error('date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Note</label>
                    <input type="text" name="note" class="form-control" value="{{ old('note', $expense->note) }}" placeholder="Optional note...">
                </div>

                <!-- Current Receipt Display -->
                @if($expense->receipt_path)
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold text-muted"><i class="bi bi-paperclip me-1"></i>Current Receipt</span>
                            <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2">
                                <i class="bi bi-box-arrow-up-right me-1"></i> View Receipt
                            </a>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_receipt" value="1" id="removeReceipt">
                            <label class="form-check-label small text-danger fw-semibold" for="removeReceipt">
                                Delete current receipt
                            </label>
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">
                        {{ $expense->receipt_path ? 'Replace Receipt (Optional)' : 'Upload Receipt (Optional)' }}
                    </label>
                    <input type="file" name="receipt" class="form-control form-control-sm" accept="image/*,application/pdf">
                    <div class="form-text small" style="font-size: 0.72rem;">Accepts JPG, PNG, PDF (Max: 2MB)</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('expenses.index') }}" class="btn btn-light rounded-3 px-4 border flex-grow-1">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm flex-grow-1">
                        <i class="bi bi-check2 me-1"></i> Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const catSelect = document.getElementById('editExpenseCategory');
    const section = document.getElementById('editExpenseTravelSection');
    const vehSection = document.getElementById('editExpenseVehicleSection');
    const toggleBtn = document.getElementById('editExpenseToggleTravelBtn');
    const toggleIcon = document.getElementById('editExpenseToggleTravelIcon');
    const clearBtn = document.getElementById('editExpenseClearTravelBtn');
    const modeRadios = document.querySelectorAll('.edit-travel-mode');
    const vehRadios = document.querySelectorAll('.edit-vehicle-type');

    function updateVehVisibility() {
        let checkedMode = null;
        modeRadios.forEach(r => { if (r.checked) checkedMode = r.value; });

        if (checkedMode && ['PickMe', 'Uber', 'Other'].includes(checkedMode)) {
            vehSection.classList.remove('d-none');
        } else {
            vehSection.classList.add('d-none');
            vehRadios.forEach(r => r.checked = false);
        }
    }

    if (catSelect) {
        catSelect.addEventListener('change', function() {
            const opt = catSelect.options[catSelect.selectedIndex];
            const catName = (opt.getAttribute('data-name') || opt.text || '').toLowerCase();
            if (catName.includes('transport') || catName.includes('travel') || catName.includes('ගමන්')) {
                section.classList.remove('d-none');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-up';
            }
        });
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const isHidden = section.classList.contains('d-none');
            if (isHidden) {
                section.classList.remove('d-none');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-up';
            } else {
                section.classList.add('d-none');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-down';
            }
        });
    }

    modeRadios.forEach(r => {
        r.addEventListener('change', updateVehVisibility);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            modeRadios.forEach(r => r.checked = false);
            vehRadios.forEach(r => r.checked = false);
            vehSection.classList.add('d-none');
        });
    }

    updateVehVisibility();
});
</script>
@endpush
@endsection
