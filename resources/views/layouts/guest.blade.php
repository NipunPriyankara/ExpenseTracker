<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4338ca">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Expense Tracker') - Personal Expense Tracker</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #4338ca;
            --primary-hover: #3730a3;
            --bg-body: #f8fafc;
            --card-border: #e2e8f0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            border: 1px solid var(--card-border);
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            background: #ffffff;
            width: 100%;
            max-width: 440px;
        }
        .brand-icon {
            width: 54px;
            height: 54px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.6rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border-color: #cbd5e1;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="auth-card mx-auto p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="brand-icon mb-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <h4 class="fw-bold mb-1">ExpenseTracker</h4>
                <p class="text-muted small">Personal Finance & Budgeting</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-warning alert-dismissible fade show rounded-3 small py-2" role="alert">
                    <i class="bi bi-info-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    @if($errors->count() == 1)
                        {{ $errors->first() }}
                    @else
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('SW Registered', reg))
                    .catch(err => console.log('SW failed', err));
            });
        }
    </script>
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
</body>
</html>
