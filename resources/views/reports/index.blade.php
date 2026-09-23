@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-12 col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line text-primary me-2"></i>Financial Reports & Analytics</h4>
        <p class="text-muted small mb-0">Period: <strong>{{ $startDate->format('M d, Y') }}</strong> - <strong>{{ $endDate->format('M d, Y') }}</strong></p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <div class="btn-group btn-group-sm rounded-pill p-1 bg-white border shadow-sm" role="group">
            <a href="?period=current_month" class="btn btn-sm rounded-pill px-3 {{ $filter === 'current_month' ? 'btn-primary' : 'btn-light' }}">
                This Month
            </a>
            <a href="?period=previous_month" class="btn btn-sm rounded-pill px-3 {{ $filter === 'previous_month' ? 'btn-primary' : 'btn-light' }}">
                Last Month
            </a>
            <button type="button" class="btn btn-sm rounded-pill px-3 {{ $filter === 'custom' ? 'btn-primary' : 'btn-light' }}" data-bs-toggle="collapse" data-bs-target="#customRangeCollapse">
                Custom Range
            </button>
        </div>
    </div>
</div>

<!-- Custom Range Collapse Form -->
<div class="collapse {{ $filter === 'custom' ? 'show' : '' }} mb-4" id="customRangeCollapse">
    <div class="card card-custom p-3">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="period" value="custom">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-muted">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}" required>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-muted">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}" required>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">Apply Date Range</button>
            </div>
        </form>
    </div>
</div>

<!-- Key Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <span class="small text-muted fw-semibold">Total Income</span>
            <h5 class="fw-bold mb-0 text-success mt-1">Rs. {{ number_format($totalIncome, 2) }}</h5>
            <small class="text-muted" style="font-size: 0.72rem;">Selected period</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <span class="small text-muted fw-semibold">Total Expenses</span>
            <h5 class="fw-bold mb-0 text-danger mt-1">Rs. {{ number_format($totalExpense, 2) }}</h5>
            <small class="text-muted" style="font-size: 0.72rem;">Selected period</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <span class="small text-muted fw-semibold">Net Balance</span>
            <h5 class="fw-bold mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }} mt-1">
                Rs. {{ number_format($netBalance, 2) }}
            </h5>
            <small class="text-muted" style="font-size: 0.72rem;">Income - Expenses</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-custom p-3 h-100">
            <span class="small text-muted fw-semibold">Transactions</span>
            <h5 class="fw-bold mb-0 text-dark mt-1">{{ $totalTransactions }}</h5>
            <small class="text-muted" style="font-size: 0.72rem;">Records analyzed</small>
        </div>
    </div>
</div>

<!-- Charts Row 1: Category Doughnut & Monthly Comparison -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-danger me-2"></i>Expenses by Category</h6>
            @if(count($expensesByCategory) > 0)
                <div style="position: relative; height: 260px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-pie-chart display-6"></i>
                    <p class="small mt-2 mb-0">No expense data in this period.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-steps text-primary me-2"></i>Income vs Expenses (Last 6 Months)</h6>
            <div style="position: relative; height: 260px;">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2: Daily Expenses Flow -->
<div class="card card-custom p-3 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-graph-up text-info me-2"></i>Daily Expense Flow</h6>
    <div style="position: relative; height: 220px;">
        <canvas id="dailyExpenseChart"></canvas>
    </div>
</div>

<!-- Category Percentage Breakdown Table -->
<div class="card card-custom p-3 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-table text-primary me-2"></i>Category Spending Breakdown</h6>
    @if(count($expensesByCategory) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th class="ps-3">CATEGORY</th>
                        <th class="text-center">RECORDS</th>
                        <th class="text-end">AMOUNT</th>
                        <th class="text-end pe-3">% OF TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expensesByCategory as $cat)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="stat-card-icon" style="width: 32px; height: 32px; background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}; font-size: 0.9rem;">
                                        <i class="bi {{ $cat['icon'] }}"></i>
                                    </div>
                                    <span class="fw-semibold text-dark">{{ $cat['name'] }}</span>
                                </div>
                            </td>
                            <td class="text-center small text-muted">{{ $cat['count'] }}</td>
                            <td class="text-end fw-bold text-danger">Rs. {{ number_format($cat['amount'], 2) }}</td>
                            <td class="text-end pe-3">
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $cat['percentage'] }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted small text-center my-3">No expenses recorded for this date range.</p>
    @endif
</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Category Doughnut Chart
    @if(count($expensesByCategory) > 0)
        const catLabels = @json($expensesByCategory->pluck('name'));
        const catData = @json($expensesByCategory->pluck('amount'));
        const catColors = @json($expensesByCategory->pluck('color'));

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: catColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12, font: { family: 'Plus Jakarta Sans', size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    @endif

    // 2. Monthly Trend Chart (Grouped Bar)
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'bar',
        data: {
            labels: @json($monthsTrend),
            datasets: [
                {
                    label: 'Income (Rs.)',
                    data: @json($monthlyIncomeData),
                    backgroundColor: '#10b981',
                    borderRadius: 6
                },
                {
                    label: 'Expense (Rs.)',
                    data: @json($monthlyExpenseData),
                    backgroundColor: '#ef4444',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 11 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                }
            }
        }
    });

    // 3. Daily Expense Chart (Line)
    new Chart(document.getElementById('dailyExpenseChart'), {
        type: 'line',
        data: {
            labels: @json($daysInPeriod),
            datasets: [{
                label: 'Daily Spent (Rs.)',
                data: @json($dailyExpenseData),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, maxTicksLimit: 15 }
                }
            }
        }
    });
});
</script>
@endpush
