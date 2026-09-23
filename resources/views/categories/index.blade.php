@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-tags-fill text-primary me-2"></i>Categories & Sub-categories</h4>
        <p class="text-muted small mb-0">Organize and manage your expense and income classifications.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg"></i>
        <span>New Category</span>
    </button>
</div>

<!-- Type Filter Tabs -->
<div class="d-flex align-items-center gap-2 mb-4 overflow-auto pb-1">
    <a href="{{ route('categories.index') }}" class="btn btn-sm rounded-pill px-3 {{ $selectedType === 'all' ? 'btn-primary' : 'btn-light border text-muted' }}">
        All Categories ({{ $expenseCount + $incomeCount }})
    </a>
    <a href="{{ route('categories.index', ['type' => 'expense']) }}" class="btn btn-sm rounded-pill px-3 {{ $selectedType === 'expense' ? 'btn-danger text-white' : 'btn-light border text-muted' }}">
        <i class="bi bi-arrow-up-right me-1"></i>Expense Categories ({{ $expenseCount }})
    </a>
    <a href="{{ route('categories.index', ['type' => 'income']) }}" class="btn btn-sm rounded-pill px-3 {{ $selectedType === 'income' ? 'btn-success text-white' : 'btn-light border text-muted' }}">
        <i class="bi bi-arrow-down-left me-1"></i>Income Categories ({{ $incomeCount }})
    </a>
</div>

<div class="row g-3">
    @forelse($categories as $cat)
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card card-custom p-3 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stat-card-icon" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }};">
                        <i class="bi {{ $cat->icon }}"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        @if($cat->type === 'income')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" style="font-size: 0.65rem;">
                                Income
                            </span>
                        @endif
                        @if($cat->parent)
                            <span class="badge bg-primary-subtle text-primary border rounded-pill small" style="font-size: 0.65rem;" title="Subcategory of {{ $cat->parent->name }}">
                                <i class="bi bi-diagram-2 me-1"></i>{{ $cat->parent->name }}
                            </span>
                        @elseif($cat->is_default)
                            <span class="badge bg-light text-muted border rounded-pill small" style="font-size: 0.68rem;">Default</span>
                        @endif
                        @if(!$cat->is_default)
                            <form method="POST" action="{{ route('categories.destroy', $cat->id) }}" onsubmit="return confirm('Delete this custom category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none ms-1" title="Delete custom category">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        @if($cat->parent)
                            <span class="text-muted small fw-normal">{{ $cat->parent->name }} ›</span>
                        @endif
                        {{ $cat->name }}
                    </h6>
                    <div class="d-flex align-items-center justify-content-between small text-muted" style="font-size: 0.75rem;">
                        <span>
                            @if($cat->type === 'income')
                                {{ $cat->incomes()->count() }} {{ Str::plural('income', $cat->incomes()->count()) }}
                            @else
                                {{ $cat->expenses()->count() }} {{ Str::plural('expense', $cat->expenses()->count()) }}
                            @endif
                        </span>
                        @if($cat->subcategories->count() > 0)
                            <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.65rem;">
                                {{ $cat->subcategories->count() }} sub-items
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-custom p-5 text-center">
                <i class="bi bi-tags text-muted display-4 mb-2"></i>
                <h5 class="fw-bold text-muted">No Categories Found</h5>
                <p class="text-muted small">Create your first custom category using the button above.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Custom Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addCategoryModalLabel">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i>Create Category / Sub-category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-body pt-3">
                    <!-- Type Selection (Expense vs Income) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted d-block">Category Type *</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="type" id="catTypeExpense" value="expense" {{ $selectedType === 'income' ? '' : 'checked' }} autocomplete="off">
                            <label class="btn btn-outline-danger btn-sm rounded-pill px-3 flex-fill" for="catTypeExpense">
                                <i class="bi bi-arrow-up-right me-1"></i> Expense Category
                            </label>

                            <input type="radio" class="btn-check" name="type" id="catTypeIncome" value="income" {{ $selectedType === 'income' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-success btn-sm rounded-pill px-3 flex-fill" for="catTypeIncome">
                                <i class="bi bi-arrow-down-left me-1"></i> Income Category
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dialog TV, Gym, Freelance Projects" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Parent Category (Optional - උප කාණ්ඩයක් කිරීමට)</label>
                        <select name="parent_id" id="modalParentCategorySelect" class="form-select">
                            <option value="">-- None (Create as Main Category) --</option>
                            @foreach($parentCategories as $parentCat)
                                <option value="{{ $parentCat->id }}" data-type="{{ $parentCat->type }}">
                                    [{{ ucfirst($parentCat->type) }}] {{ $parentCat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small" style="font-size: 0.72rem;">උදා: "Bills" තේරුවහොත් එය "Bills" යටතේ උප කාණ්ඩයක් ලෙස හැදේ.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Icon</label>
                            <select name="icon" id="modalCategoryIconSelect" class="form-select">
                                <option value="bi-tag" selected>Tag (bi-tag)</option>
                                <option value="bi-cash-coin">Cash Coin (bi-cash-coin)</option>
                                <option value="bi-cash-stack">Cash Stack (bi-cash-stack)</option>
                                <option value="bi-bank">Bank (bi-bank)</option>
                                <option value="bi-piggy-bank">Savings (bi-piggy-bank)</option>
                                <option value="bi-stars">Bonus (bi-stars)</option>
                                <option value="bi-laptop">Laptop/Tech (bi-laptop)</option>
                                <option value="bi-shop">Shop/Store (bi-shop)</option>
                                <option value="bi-phone">Phone / Mobile (bi-phone)</option>
                                <option value="bi-wifi">Wifi / Internet (bi-wifi)</option>
                                <option value="bi-tv">TV / Media (bi-tv)</option>
                                <option value="bi-receipt">Bills (bi-receipt)</option>
                                <option value="bi-gift">Gift (bi-gift)</option>
                                <option value="bi-heart">Health/Care (bi-heart)</option>
                                <option value="bi-cup-straw">Coffee/Drinks (bi-cup-straw)</option>
                                <option value="bi-airplane">Travel (bi-airplane)</option>
                                <option value="bi-cart">Groceries (bi-cart)</option>
                                <option value="bi-tools">Repairs (bi-tools)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Badge Color</label>
                            <input type="color" name="color" id="modalCategoryColorInput" class="form-control form-control-color w-100" value="{{ $selectedType === 'income' ? '#10b981' : '#4f46e5' }}" title="Choose color">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check2 me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const parentSelect = document.getElementById('modalParentCategorySelect');
    const colorInput = document.getElementById('modalCategoryColorInput');
    const iconSelect = document.getElementById('modalCategoryIconSelect');

    function filterParents() {
        if (!parentSelect) return;
        const selectedType = document.querySelector('input[name="type"]:checked')?.value || 'expense';

        Array.from(parentSelect.options).forEach(opt => {
            if (!opt.value) return; // keep "None"
            const optType = opt.getAttribute('data-type');
            if (optType === selectedType) {
                opt.style.display = '';
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                if (opt.selected) {
                    parentSelect.value = '';
                }
            }
        });

        // Set default color/icon based on type if not changed
        if (selectedType === 'income') {
            if (colorInput && colorInput.value === '#4f46e5') colorInput.value = '#10b981';
            if (iconSelect && iconSelect.value === 'bi-tag') iconSelect.value = 'bi-cash-coin';
        } else {
            if (colorInput && colorInput.value === '#10b981') colorInput.value = '#4f46e5';
            if (iconSelect && iconSelect.value === 'bi-cash-coin') iconSelect.value = 'bi-tag';
        }
    }

    typeRadios.forEach(r => r.addEventListener('change', filterParents));
    filterParents();
});
</script>
@endsection
