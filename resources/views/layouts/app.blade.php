<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4338ca">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <title>@yield('title', 'Dashboard') - Personal Expense Tracker</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #4338ca;
            --primary-hover: #3730a3;
            --primary-light: #e0e7ff;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --bg-body: #f8fafc;
            --card-border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 75px; /* space for mobile bottom bar */
        }

        @media (min-width: 992px) {
            body {
                padding-bottom: 25px;
            }
        }

        .navbar-main {
            background: #ffffff;
            border-bottom: 1px solid var(--card-border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .brand-badge {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.15rem;
            box-shadow: 0 3px 8px rgba(79, 70, 229, 0.25);
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-muted);
            padding: 0.5rem 0.75rem !important;
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
            font-size: 0.92rem;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .card-custom {
            border: 1px solid var(--card-border);
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 4px 6px -2px rgba(0,0,0,0.01);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .stat-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Floating Bottom Navigation for Mobile */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #ffffff;
            border-top: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1030;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 500;
            flex: 1;
            padding: 6px 0;
            transition: color 0.15s;
        }

        .bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }

        .bottom-nav-item.active, .bottom-nav-item:hover {
            color: var(--primary);
        }

        .btn-floating-add {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            margin-top: -26px;
            border: 3px solid #ffffff;
            transition: transform 0.2s;
        }

        .btn-floating-add:hover, .btn-floating-add:focus {
            color: #ffffff;
            transform: scale(1.06);
        }

        @media (min-width: 992px) {
            .mobile-bottom-nav {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Desktop / Tablet Navbar -->
    <nav class="navbar navbar-expand-lg navbar-main sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('dashboard') }}">
                <div class="brand-badge">
                    <i class="bi bi-wallet2"></i>
                </div>
                <span>ExpenseTracker</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="bi bi-list fs-3"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-1x2-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                            <i class="bi bi-receipt me-1"></i> Expenses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}" href="{{ route('incomes.index') }}">
                            <i class="bi bi-wallet2 me-1"></i> Income
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}" href="{{ route('budgets.index') }}">
                            <i class="bi bi-pie-chart me-1"></i> Budgets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('savings.*') ? 'active' : '' }}" href="{{ route('savings.index') }}">
                            <i class="bi bi-piggy-bank me-1"></i> Savings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}" href="{{ route('loans.index') }}">
                            <i class="bi bi-bank me-1"></i> Loans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="bi bi-bar-chart-line me-1"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                            <i class="bi bi-clock-history me-1"></i> History
                        </a>
                    </li>
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.*') ? 'active text-white bg-danger' : 'text-danger' }} fw-semibold rounded-pill px-3" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-shield-lock-fill me-1"></i> Admin
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-success btn-sm text-white rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#globalAddIncomeModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Income</span>
                    </button>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Expense</span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill dropdown-toggle d-flex align-items-center gap-2 px-3 border shadow-sm" type="button" data-bs-toggle="dropdown">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle object-fit-cover shadow-sm" style="width: 24px; height: 24px;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                    {{ Auth::user()->initials }}
                                </div>
                            @endif
                            <span class="fw-semibold">{{ Auth::user()->name ?? 'User' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 mt-2">
                            <li><h6 class="dropdown-header small">{{ Auth::user()->email ?? '' }}</h6></li>
                            @if(Auth::check() && Auth::user()->isAdmin())
                                <li>
                                    <a class="dropdown-item small text-danger fw-semibold {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-shield-lock-fill me-2"></i>Admin Panel
                                    </a>
                                </li>
                            @endif
                            <li><a class="dropdown-item small {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}"><i class="bi bi-person-gear me-2 text-primary"></i>Profile & Account Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="{{ route('savings.index') }}"><i class="bi bi-piggy-bank me-2 text-success"></i>Savings & Goals</a></li>
                            <li><a class="dropdown-item small" href="{{ route('loans.index') }}"><i class="bi bi-bank me-2 text-info"></i>Loan & Debt Manager</a></li>
                            <li><a class="dropdown-item small" href="{{ route('recurring.index') }}"><i class="bi bi-repeat me-2 text-warning"></i>Recurring Expenses</a></li>
                            <li><a class="dropdown-item small" href="{{ route('categories.index') }}"><i class="bi bi-tags me-2 text-primary"></i>Manage Categories</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="container my-4 flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 mb-4 small shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 mb-4 small shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Floating Mobile Bottom Navigation -->
    <div class="mobile-bottom-nav">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('expenses.index') }}" class="bottom-nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span>Expenses</span>
        </a>
        <a href="#quickAddModal" class="btn-floating-add text-decoration-none" data-bs-toggle="modal" role="button" aria-label="Quick Add Expense">
            <i class="bi bi-plus-lg"></i>
        </a>
        <a href="{{ route('incomes.index') }}" class="bottom-nav-item {{ request()->routeIs('incomes.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>
            <span>Income</span>
        </a>
        <a href="#offcanvasMore" class="bottom-nav-item" data-bs-toggle="offcanvas" role="button">
            <i class="bi bi-grid"></i>
            <span>More</span>
        </a>
    </div>

    <!-- Mobile More Drawer -->
    <div class="offcanvas offcanvas-bottom rounded-top-4" tabindex="-1" id="offcanvasMore" style="height: auto; max-height: 85vh;">
        <div class="offcanvas-header pb-2 border-bottom">
            <h6 class="offcanvas-title fw-bold"><i class="bi bi-grid-fill text-primary me-2"></i>Navigation Menu</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-3">
            <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded-3">
                <div class="d-flex align-items-center gap-3">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle object-fit-cover shadow-sm" style="width: 44px; height: 44px;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 44px; height: 44px;">
                            {{ Auth::user()->initials }}
                        </div>
                    @endif
                    <div>
                        <div class="fw-bold">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                </div>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 small">
                    <i class="bi bi-gear-fill me-1"></i> Edit
                </a>
            </div>

            <div class="list-group list-group-flush rounded-3 border mb-3">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 text-danger fw-bold {{ request()->routeIs('admin.*') ? 'active text-white' : '' }}">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                        <span>Admin Panel</span>
                    </a>
                @endif
                <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 {{ request()->routeIs('profile.*') ? 'active text-white' : '' }}">
                    <i class="bi bi-person-gear text-primary fs-5"></i>
                    <span>Profile & Account Settings</span>
                </a>
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-speedometer2 text-primary fs-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('expenses.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-receipt text-danger fs-5"></i>
                    <span>Expenses</span>
                </a>
                <a href="{{ route('incomes.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-wallet2 text-success fs-5"></i>
                    <span>Income</span>
                </a>
                <a href="{{ route('budgets.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-pie-chart text-warning fs-5"></i>
                    <span>Monthly Budgets</span>
                </a>
                <a href="{{ route('savings.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-piggy-bank text-success fs-5"></i>
                    <span>Savings & Goals (ඉතිරි කිරීම්)</span>
                </a>
                <a href="{{ route('loans.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-bank text-primary fs-5"></i>
                    <span>Loan & Debt Manager (ණය)</span>
                </a>
                <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-bar-chart-line text-info fs-5"></i>
                    <span>Reports & Analytics</span>
                </a>
                <a href="{{ route('transactions.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-clock-history text-secondary fs-5"></i>
                    <span>All Transactions & Export</span>
                </a>
                <a href="{{ route('recurring.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-repeat text-warning fs-5"></i>
                    <span>Recurring Expenses</span>
                </a>
                <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                    <i class="bi bi-tags-fill text-info fs-5"></i>
                    <span>Categories & Sub-categories</span>
                </a>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded-3 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-right me-2"></i> Log Out
                </button>
            </form>
        </div>
    </div>

    <!-- Universal Global Expense Modal (Supports Single & Split / Itemized Bill) -->
    <div class="modal fade" id="quickAddModal" tabindex="-1" aria-labelledby="quickAddModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="brand-badge bg-danger text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-receipt fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="quickAddModalLabel">Record Expense</h5>
                            <p class="text-muted small mb-0">Record single or itemized bill payments</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                @php
                    $globalCategories = Auth::check()
                        ? \App\Models\Category::forUser(Auth::id())->where('type', 'expense')->with(['parent', 'subcategories'])->orderBy('name')->get()
                        : collect();
                    $globalCatData = [];
                    foreach($globalCategories as $c) {
                        $globalCatData[] = [
                            'id' => $c->id,
                            'name' => $c->name,
                            'display_name' => $c->display_name,
                            'parent_id' => $c->parent_id,
                        ];
                    }
                    $globalCategoriesJson = json_encode($globalCatData);
                @endphp

                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" id="globalExpenseForm">
                    @csrf
                    <div class="modal-body pt-3">

                        <!-- Split / Itemized Bill Toggle Banner -->
                        <div class="d-flex align-items-center justify-content-between p-2 px-3 mb-3 rounded-3 border" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-receipt-cutoff text-danger fs-5"></i>
                                <div>
                                    <span class="fw-bold small text-dark d-block">Split / Itemized Bill (බිල්පතේ අයිතම වෙන් වෙන්ව දැමීම)</span>
                                    <span class="text-muted" style="font-size: 0.73rem;">Record multiple breakdown items (e.g. Mobile, Router, TV) under their own categories in one go</span>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="globalSplitToggle" name="is_split" value="1" style="cursor: pointer; transform: scale(1.2);">
                            </div>
                        </div>

                        <!-- ================= INLINE CATEGORY / SUB-CATEGORY CREATOR ================= -->
                        <div id="inlineCatCard" class="card border border-primary p-3 mb-3 rounded-3 shadow-sm bg-light-subtle d-none" style="border-left: 4px solid #4f46e5 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-primary d-flex align-items-center gap-1">
                                    <i class="bi bi-tags-fill"></i> New Category / Sub-category (අලුත් කාණ්ඩයක් හෝ උප කාණ්ඩයක් සාදන්න)
                                </span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" id="closeInlineCatBtn" title="Close">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div id="inlineCatAlert" class="alert alert-danger py-1 px-2 small d-none mb-2"></div>

                            <div class="row g-2">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold text-muted mb-1">Name *</label>
                                    <input type="text" id="inlineCatName" class="form-control form-control-sm" placeholder="e.g. Dialog TV, Dialog Mobile, Gym...">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Parent Category (උප කාණ්ඩයක් කිරීමට)</label>
                                    <select id="inlineCatParent" class="form-select form-select-sm">
                                        <option value="">-- None (Create as Main Category) --</option>
                                        @foreach($globalCategories->whereNull('parent_id') as $pCat)
                                            <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small" style="font-size: 0.68rem;">උදා: "Bills" තේරුවහොත් "Bills › Dialog TV" ලෙස හැදේ.</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Badge Color</label>
                                    <input type="color" id="inlineCatColor" class="form-control form-control-color form-control-sm w-100" value="#4f46e5" title="Choose color">
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" id="cancelInlineCatBtn">Cancel</button>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" id="saveInlineCatBtn">
                                        <i class="bi bi-check2 me-1"></i> Save & Use Category
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ================= SINGLE EXPENSE MODE ================= -->
                        <div id="globalSingleFields">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Amount (Rs.) *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light fw-bold text-danger border-end-0">Rs.</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="globalExpenseAmount" class="form-control fw-bold fs-4 border-start-0" placeholder="0.00" required autofocus>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-semibold text-muted mb-0">Category *</label>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary trigger-inline-cat-btn" data-target-select="globalExpenseCategory" style="font-size: 0.74rem;">
                                            <i class="bi bi-plus-circle me-1"></i>New Category / Sub-category
                                        </button>
                                    </div>
                                    <select name="category_id" id="globalExpenseCategory" class="form-select category-select-input" required>
                                        <option value="" disabled selected>Select category</option>
                                        @foreach($globalCategories as $category)
                                            <option value="{{ $category->id }}" data-name="{{ strtolower($category->name) }}">
                                                {{ $category->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Note (Optional)</label>
                                    <input type="text" name="note" class="form-control" placeholder="e.g. Lunch at Cafe, PickMe to office...">
                                </div>
                            </div>

                            <!-- Travel Details Toggle Link -->
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary" id="globalToggleTravelBtn" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt"></i> Travel Details (Bus, PickMe, Uber) <i class="bi bi-chevron-down" id="globalToggleTravelIcon"></i>
                                </button>
                            </div>

                            <!-- Travel Details Box -->
                            <div id="globalTravelSection" class="p-3 mb-3 rounded-3 border bg-light-subtle d-none" style="border-left: 4px solid #0ea5e9 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <span class="small fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-compass text-primary"></i> Travel / Transport Details (ගමන් විස්තර)
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" id="globalClearTravelBtn" style="font-size: 0.72rem;">
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
                                            <input type="radio" class="btn-check global-travel-mode" name="transport_mode" id="gt_mode_bus" value="Bus" autocomplete="off">
                                            <label class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" for="gt_mode_bus">
                                                <i class="bi bi-bus-front me-1"></i> Bus (බස්)
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-travel-mode" name="transport_mode" id="gt_mode_pickme" value="PickMe" autocomplete="off">
                                            <label class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 py-1" for="gt_mode_pickme">
                                                <i class="bi bi-geo-alt-fill text-warning me-1"></i> PickMe
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-travel-mode" name="transport_mode" id="gt_mode_uber" value="Uber" autocomplete="off">
                                            <label class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1" for="gt_mode_uber">
                                                <i class="bi bi-car-front-fill me-1"></i> Uber
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-travel-mode" name="transport_mode" id="gt_mode_train" value="Train" autocomplete="off">
                                            <label class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 py-1" for="gt_mode_train">
                                                <i class="bi bi-train-front me-1"></i> Train
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-travel-mode" name="transport_mode" id="gt_mode_other" value="Other" autocomplete="off">
                                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="gt_mode_other">
                                                <i class="bi bi-three-dots me-1"></i> Other
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle selection (PickMe or Uber) -->
                                <div id="globalVehicleSection" class="mt-2 pt-2 border-top d-none">
                                    <label class="form-label small fw-semibold text-muted d-block mb-1">
                                        Vehicle Type (PickMe / Uber වාහනය):
                                    </label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div>
                                            <input type="radio" class="btn-check global-vehicle-type" name="vehicle_type" id="gt_veh_3wheel" value="3-Wheel" autocomplete="off">
                                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="gt_veh_3wheel">
                                                🛺 3-Wheel (Tuk)
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-vehicle-type" name="vehicle_type" id="gt_veh_bike" value="Bike" autocomplete="off">
                                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="gt_veh_bike">
                                                🏍️ Bike (යතුරුපැදි)
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-vehicle-type" name="vehicle_type" id="gt_veh_car" value="Car" autocomplete="off">
                                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="gt_veh_car">
                                                🚗 Car (කාර්)
                                            </label>
                                        </div>
                                        <div>
                                            <input type="radio" class="btn-check global-vehicle-type" name="vehicle_type" id="gt_veh_van" value="Van" autocomplete="off">
                                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" for="gt_veh_van">
                                                🚐 Van (වෑන්)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= SPLIT / ITEMIZED EXPENSE MODE ================= -->
                        <div id="globalSplitFields" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <div>
                                    <span class="fw-bold text-dark small"><i class="bi bi-list-check text-danger me-1"></i>Bill Items Breakdown</span>
                                    <span class="text-muted ms-2 small" id="globalSplitItemCountText">(0 items)</span>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-primary trigger-inline-cat-btn" style="font-size: 0.74rem;">
                                    <i class="bi bi-plus-circle me-1"></i>New Category / Sub-category
                                </button>
                            </div>

                            <!-- Dynamic Items List -->
                            <div id="globalSplitItemsList" class="d-flex flex-column gap-2 mb-3">
                                <!-- Items rows inserted dynamically via JS -->
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" id="globalAddSplitItemBtn">
                                    <i class="bi bi-plus-lg me-1"></i> Add Another Item (තවත් අයිතමයක්)
                                </button>
                                <div class="text-end">
                                    <span class="text-muted small me-2">Calculated Total:</span>
                                    <span class="fw-bold fs-5 text-danger" id="globalSplitCalculatedTotal">Rs. 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- ================= SHARED DETAILS (Both Single & Split) ================= -->
                        <div class="row g-2 mb-3 pt-2 border-top">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted">Payment Method *</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="Online Payment" selected>Online Payment</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank Transfer</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted">Date *</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted">Receipt / Bill Image (Optional)</label>
                            <input type="file" name="receipt" class="form-control form-control-sm" accept="image/*,application/pdf">
                            <div class="form-text small" style="font-size: 0.72rem;">Attach bill/payment screenshot (will be attached to all items). Accepts JPG, PNG, PDF (Max: 2MB)</div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-3 px-4 shadow-sm" id="globalSaveExpenseBtn">
                            <i class="bi bi-check2 me-1"></i> Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Global Add Income Modal -->
    <div class="modal fade" id="globalAddIncomeModal" tabindex="-1" aria-labelledby="globalAddIncomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="brand-badge bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="globalAddIncomeModalLabel">Record Income</h5>
                            <p class="text-muted small mb-0">Record single income deposit</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                @php
                    $globalIncomeCategories = Auth::check()
                        ? \App\Models\Category::forUser(Auth::id())->where('type', 'income')->with('parent')->orderBy('name')->get()
                        : collect();
                @endphp
                <form method="POST" action="{{ route('incomes.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Amount (Rs.) *</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold text-success border-end-0">Rs.</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold fs-4 border-start-0" placeholder="0.00" required autofocus>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted">Category & Subcategory *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" disabled selected>Select category</option>
                                    @foreach($globalIncomeCategories as $incomeCat)
                                        <option value="{{ $incomeCat->id }}">
                                            {{ $incomeCat->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted">Date *</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted">Description / Note (Optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="e.g. Monthly salary, client project payout...">
                        </div>

                        <div class="text-end pt-1">
                            <a href="{{ route('incomes.index') }}" class="small text-success text-decoration-none">
                                <i class="bi bi-receipt-cutoff me-1"></i>Need multiple / itemized income? Open Income Manager &raquo;
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success text-white rounded-3 px-4 shadow-sm">
                            <i class="bi bi-check2 me-1"></i> Save Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("service-worker.js") }}')
                    .then(reg => console.log('PWA Service Worker registered:', reg.scope))
                    .catch(err => console.log('PWA Service Worker registration notice:', err));
            });
        }
    </script>
    <!-- Global Double-Submit & Multi-Click Protection -->
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }
            form.dataset.submitting = 'true';
            
            const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitBtns.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
                if (btn.tagName.toLowerCase() === 'button') {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
                }
            });
        });
    </script>

    <!-- Global Unified Expense Modal Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Alias any button targeting #addExpenseModal to open #quickAddModal
            document.querySelectorAll('[data-bs-target="#addExpenseModal"]').forEach(btn => {
                btn.setAttribute('data-bs-target', '#quickAddModal');
            });

            // Travel Tracker
            const catSelect = document.getElementById('globalExpenseCategory');
            const section = document.getElementById('globalTravelSection');
            const vehSection = document.getElementById('globalVehicleSection');
            const toggleBtn = document.getElementById('globalToggleTravelBtn');
            const toggleIcon = document.getElementById('globalToggleTravelIcon');
            const clearBtn = document.getElementById('globalClearTravelBtn');
            const modeRadios = document.querySelectorAll('.global-travel-mode');
            const vehRadios = document.querySelectorAll('.global-vehicle-type');

            function updateVehVisibility() {
                let checkedMode = null;
                modeRadios.forEach(r => { if (r.checked) checkedMode = r.value; });

                if (checkedMode && ['PickMe', 'Uber', 'Other'].includes(checkedMode)) {
                    if (vehSection) vehSection.classList.remove('d-none');
                } else {
                    if (vehSection) {
                        vehSection.classList.add('d-none');
                        vehRadios.forEach(r => r.checked = false);
                    }
                }
            }

            if (catSelect && section) {
                catSelect.addEventListener('change', function() {
                    const opt = catSelect.options[catSelect.selectedIndex];
                    const catName = (opt ? (opt.getAttribute('data-name') || opt.text || '') : '').toLowerCase();
                    if (catName.includes('transport') || catName.includes('travel') || catName.includes('ගමන්')) {
                        section.classList.remove('d-none');
                        if (toggleIcon) toggleIcon.className = 'bi bi-chevron-up';
                    }
                });
            }

            if (toggleBtn && section) {
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

            if (clearBtn && section && vehSection) {
                clearBtn.addEventListener('click', function() {
                    modeRadios.forEach(r => r.checked = false);
                    vehRadios.forEach(r => r.checked = false);
                    vehSection.classList.add('d-none');
                });
            }

            // -------------------------------------------------------------
            // SPLIT / ITEMIZED BILL BUILDER
            // -------------------------------------------------------------
            const splitToggle = document.getElementById('globalSplitToggle');
            const singleSection = document.getElementById('globalSingleFields');
            const splitSection = document.getElementById('globalSplitFields');
            const singleAmount = document.getElementById('globalExpenseAmount');
            const singleCategory = document.getElementById('globalExpenseCategory');
            const splitList = document.getElementById('globalSplitItemsList');
            const addSplitItemBtn = document.getElementById('globalAddSplitItemBtn');
            const splitTotalEl = document.getElementById('globalSplitCalculatedTotal');
            const itemCountText = document.getElementById('globalSplitItemCountText');
            const saveSubmitBtn = document.getElementById('globalSaveExpenseBtn');

            // Categories dataset
            let availableCategories = {!! $globalCategoriesJson ?? '[]' !!};
            let splitItemIndex = 0;
            let currentActiveTargetSelect = null;

            function buildCategoryOptionsHtml(selectedId = '') {
                let html = '<option value="" disabled ' + (!selectedId ? 'selected' : '') + '>Select category</option>';
                availableCategories.forEach(cat => {
                    const isSel = (cat.id == selectedId) ? 'selected' : '';
                    html += `<option value="${cat.id}" ${isSel}>${cat.display_name}</option>`;
                });
                return html;
            }

            function addSplitRow(defaultNote = '', defaultAmount = '', defaultCatId = '') {
                if (!splitList) return;
                const idx = splitItemIndex;
                splitItemIndex++;

                const rowDiv = document.createElement('div');
                rowDiv.className = 'card p-2 border bg-light-subtle rounded-3 shadow-none split-item-row mb-2';
                rowDiv.id = `global_split_row_${idx}`;
                rowDiv.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <input type="text" name="items[${idx}][note]" class="form-control form-control-sm" placeholder="Description (e.g. Mobile, Router, TV)" value="${defaultNote}" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="input-group input-group-sm">
                                <select name="items[${idx}][category_id]" id="split_cat_select_${idx}" class="form-select form-select-sm category-select-input split-row-select" required>
                                    ${buildCategoryOptionsHtml(defaultCatId)}
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-sm px-2 row-inline-cat-btn" data-target-id="split_cat_select_${idx}" title="Create new subcategory for this item">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-5 col-md-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold text-danger px-1">Rs.</span>
                                <input type="number" step="0.01" min="0.01" name="items[${idx}][amount]" class="form-control form-control-sm fw-bold split-amount-input" placeholder="0.00" value="${defaultAmount}" required>
                            </div>
                        </div>
                        <div class="col-1 col-md-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle remove-split-row-btn" title="Remove item" style="width: 28px; height: 28px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;

                splitList.appendChild(rowDiv);
                bindSplitRowEvents(rowDiv);
                recalcSplitTotal();
            }

            function bindSplitRowEvents(rowDiv) {
                const removeBtn = rowDiv.querySelector('.remove-split-row-btn');
                const amountInput = rowDiv.querySelector('.split-amount-input');
                const rowCatBtn = rowDiv.querySelector('.row-inline-cat-btn');

                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        if (splitList.querySelectorAll('.split-item-row').length <= 1) {
                            alert('A split bill must contain at least 1 item.');
                            return;
                        }
                        rowDiv.remove();
                        recalcSplitTotal();
                    });
                }

                if (amountInput) {
                    amountInput.addEventListener('input', recalcSplitTotal);
                }

                if (rowCatBtn) {
                    rowCatBtn.addEventListener('click', function() {
                        const targetId = rowCatBtn.getAttribute('data-target-id');
                        currentActiveTargetSelect = document.getElementById(targetId);
                        openInlineCatCard();
                    });
                }
            }

            function recalcSplitTotal() {
                if (!splitList || !splitTotalEl) return;
                const rows = splitList.querySelectorAll('.split-item-row');
                let total = 0;
                rows.forEach(r => {
                    const amtInput = r.querySelector('.split-amount-input');
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

            function toggleSplitMode() {
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
                    splitList.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    if (saveSubmitBtn) saveSubmitBtn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Save All Itemized Expenses';

                    // If empty, pre-populate 3 sample items
                    if (splitList.querySelectorAll('.split-item-row').length === 0) {
                        addSplitRow('Dialog Mobile Postpaid', '1303.71');
                        addSplitRow('Dialog Home Broadband (Router)', '1936.00');
                        addSplitRow('Dialog TV', '570.00');
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
                    splitList.querySelectorAll('input, select').forEach(el => el.disabled = true);
                    if (saveSubmitBtn) saveSubmitBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save Expense';
                }
            }

            if (splitToggle) {
                splitToggle.addEventListener('change', toggleSplitMode);
            }
            if (addSplitItemBtn) {
                addSplitItemBtn.addEventListener('click', function() {
                    addSplitRow('', '');
                });
            }

            // -------------------------------------------------------------
            // INLINE CATEGORY / SUB-CATEGORY CREATOR (No nested modal bugs!)
            // -------------------------------------------------------------
            const inlineCatCard = document.getElementById('inlineCatCard');
            const inlineCatName = document.getElementById('inlineCatName');
            const inlineCatParent = document.getElementById('inlineCatParent');
            const inlineCatColor = document.getElementById('inlineCatColor');
            const inlineCatAlert = document.getElementById('inlineCatAlert');
            const saveInlineCatBtn = document.getElementById('saveInlineCatBtn');
            const closeInlineCatBtn = document.getElementById('closeInlineCatBtn');
            const cancelInlineCatBtn = document.getElementById('cancelInlineCatBtn');

            function openInlineCatCard() {
                if (!inlineCatCard) return;
                inlineCatCard.classList.remove('d-none');
                if (inlineCatAlert) inlineCatAlert.classList.add('d-none');
                if (inlineCatName) {
                    inlineCatName.value = '';
                    inlineCatName.focus();
                }
                inlineCatCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function closeInlineCatCard() {
                if (!inlineCatCard) return;
                inlineCatCard.classList.add('d-none');
                if (inlineCatAlert) inlineCatAlert.classList.add('d-none');
            }

            document.querySelectorAll('.trigger-inline-cat-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = btn.getAttribute('data-target-select');
                    currentActiveTargetSelect = targetId ? document.getElementById(targetId) : null;
                    openInlineCatCard();
                });
            });

            if (closeInlineCatBtn) closeInlineCatBtn.addEventListener('click', closeInlineCatCard);
            if (cancelInlineCatBtn) cancelInlineCatBtn.addEventListener('click', closeInlineCatCard);

            if (saveInlineCatBtn) {
                saveInlineCatBtn.addEventListener('click', function() {
                    const name = inlineCatName ? inlineCatName.value.trim() : '';
                    if (!name) {
                        if (inlineCatAlert) {
                            inlineCatAlert.textContent = 'Please enter a category or sub-category name.';
                            inlineCatAlert.classList.remove('d-none');
                        }
                        if (inlineCatName) inlineCatName.focus();
                        return;
                    }

                    const parentId = inlineCatParent ? inlineCatParent.value : '';
                    const color = inlineCatColor ? inlineCatColor.value : '#4f46e5';

                    saveInlineCatBtn.disabled = true;
                    saveInlineCatBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('name', name);
                    if (parentId) formData.append('parent_id', parentId);
                    formData.append('color', color);
                    formData.append('type', 'expense');

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
                        saveInlineCatBtn.disabled = false;
                        saveInlineCatBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save & Use Category';

                        if (data.success && data.category) {
                            const newCat = data.category;
                            availableCategories.push({
                                id: newCat.id,
                                name: newCat.name,
                                display_name: newCat.display_name,
                                parent_id: newCat.parent_id
                            });

                            // Update all category dropdowns in the modal
                            document.querySelectorAll('.category-select-input').forEach(sel => {
                                const opt = document.createElement('option');
                                opt.value = newCat.id;
                                opt.textContent = newCat.display_name;
                                sel.appendChild(opt);
                            });

                            // If main category, also add to inline parent selector
                            if (!newCat.parent_id && inlineCatParent) {
                                const opt = document.createElement('option');
                                opt.value = newCat.id;
                                opt.textContent = newCat.name;
                                inlineCatParent.appendChild(opt);
                            }

                            // Select newly created category on active target
                            if (currentActiveTargetSelect) {
                                currentActiveTargetSelect.value = newCat.id;
                                currentActiveTargetSelect.dispatchEvent(new Event('change'));
                            } else {
                                const allSplitSelects = document.querySelectorAll('.split-row-select');
                                if (allSplitSelects.length > 0) {
                                    allSplitSelects[allSplitSelects.length - 1].value = newCat.id;
                                }
                            }

                            closeInlineCatCard();
                        } else {
                            if (inlineCatAlert) {
                                inlineCatAlert.textContent = data.message || 'Error saving category.';
                                inlineCatAlert.classList.remove('d-none');
                            }
                        }
                    })
                    .catch(err => {
                        saveInlineCatBtn.disabled = false;
                        saveInlineCatBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save & Use Category';
                        if (inlineCatAlert) {
                            inlineCatAlert.textContent = 'Server error. Please verify input.';
                            inlineCatAlert.classList.remove('d-none');
                        }
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
