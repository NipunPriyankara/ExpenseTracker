<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\LoanPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $type = $request->input('type'); // all, expense, income, settlement
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $paymentMethod = $request->input('payment_method');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');

        $transactions = collect();

        // 1. Fetch Expenses (if type is not income or settlement)
        if ($type !== 'income' && $type !== 'settlement') {
            $expenseQuery = Expense::with('category')->where('user_id', $userId);

            if ($search) {
                $expenseQuery->where(function ($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%")
                      ->orWhere('payment_method', 'like', "%{$search}%")
                      ->orWhere('transport_mode', 'like', "%{$search}%")
                      ->orWhere('vehicle_type', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($catQ) use ($search) {
                          $catQ->where('name', 'like', "%{$search}%");
                      });
                });
            }
            if ($categoryId) {
                $expenseQuery->where('category_id', $categoryId);
            }
            if ($paymentMethod) {
                $expenseQuery->where('payment_method', $paymentMethod);
            }
            if ($startDate) {
                $expenseQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $expenseQuery->whereDate('date', '<=', $endDate);
            }
            if ($minAmount) {
                $expenseQuery->where('amount', '>=', $minAmount);
            }
            if ($maxAmount) {
                $expenseQuery->where('amount', '<=', $maxAmount);
            }

            $rawExpenses = $expenseQuery->get();

            // Group split expenses by split_group_id so split bills appear as 1 line
            $groupedExpenses = $rawExpenses->groupBy(function ($item) {
                return $item->split_group_id ?: ('single_' . $item->id);
            });

            $mappedExpenses = $groupedExpenses->map(function ($items, $groupKey) {
                $first = $items->first();
                $isSplit = str_starts_with($groupKey, 'split_') && $items->count() > 1;

                if ($isSplit) {
                    return [
                        'id' => $first->id,
                        'type' => 'expense',
                        'is_split' => true,
                        'split_items' => $items->map(function ($it) {
                            return [
                                'category_name' => $it->category ? $it->category->display_name : 'Uncategorized',
                                'color' => $it->category ? $it->category->color : '#4f46e5',
                                'icon' => $it->category ? $it->category->icon : 'bi-tag',
                                'note' => $it->note,
                                'amount' => (float)$it->amount,
                            ];
                        })->toArray(),
                        'date' => $first->date,
                        'category' => 'Split Bill (' . $items->count() . ' items)',
                        'icon' => 'bi-receipt-cutoff',
                        'color' => '#4f46e5',
                        'note' => null,
                        'payment_method' => $first->payment_method,
                        'transport_mode' => null,
                        'vehicle_type' => null,
                        'amount' => (float)$items->sum('amount'),
                    ];
                }

                return [
                    'id' => $first->id,
                    'type' => 'expense',
                    'is_split' => false,
                    'split_items' => [],
                    'date' => $first->date,
                    'category' => $first->category ? $first->category->display_name : 'Uncategorized',
                    'icon' => $first->category ? $first->category->icon : 'bi-receipt',
                    'color' => $first->category ? $first->category->color : '#ef4444',
                    'note' => $first->note,
                    'payment_method' => $first->payment_method,
                    'transport_mode' => $first->transport_mode,
                    'vehicle_type' => $first->vehicle_type,
                    'amount' => (float)$first->amount,
                ];
            })->values();

            $transactions = $transactions->merge($mappedExpenses);
        }

        // 2. Fetch Incomes (if type is not expense or settlement)
        if ($type !== 'expense' && $type !== 'settlement') {
            $incomeQuery = Income::with('category.parent')->where('user_id', $userId);

            if ($search) {
                $incomeQuery->where(function ($q) use ($search) {
                    $q->where('source', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($catQ) use ($search) {
                          $catQ->where('name', 'like', "%{$search}%");
                      });
                });
            }
            if ($categoryId) {
                $incomeQuery->where('category_id', $categoryId);
            }
            if ($startDate) {
                $incomeQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $incomeQuery->whereDate('date', '<=', $endDate);
            }
            if ($minAmount) {
                $incomeQuery->where('amount', '>=', $minAmount);
            }
            if ($maxAmount) {
                $incomeQuery->where('amount', '<=', $maxAmount);
            }

            $rawIncomes = $incomeQuery->get();

            $groupedIncomes = $rawIncomes->groupBy(function ($item) {
                return $item->split_group_id ?: ('single_' . $item->id);
            });

            $mappedIncomes = $groupedIncomes->map(function ($items, $groupKey) {
                $first = $items->first();
                $isSplit = str_starts_with($groupKey, 'split_inc_') && $items->count() > 1;

                if ($isSplit) {
                    return [
                        'id' => $first->id,
                        'type' => 'income',
                        'is_split' => true,
                        'split_items' => $items->map(function ($it) {
                            return [
                                'category_name' => $it->category ? $it->category->display_name : ($it->source ?: 'Income'),
                                'color' => $it->category ? $it->category->color : '#10b981',
                                'icon' => $it->category ? $it->category->icon : 'bi-cash-coin',
                                'note' => $it->note,
                                'amount' => (float)$it->amount,
                            ];
                        })->toArray(),
                        'date' => $first->date,
                        'category' => 'Split Income (' . $items->count() . ' items)',
                        'icon' => 'bi-receipt-cutoff',
                        'color' => '#10b981',
                        'note' => null,
                        'payment_method' => 'Income Deposit',
                        'transport_mode' => null,
                        'vehicle_type' => null,
                        'amount' => (float)$items->sum('amount'),
                    ];
                }

                return [
                    'id' => $first->id,
                    'type' => 'income',
                    'is_split' => false,
                    'date' => $first->date,
                    'category' => $first->category ? $first->category->display_name : ($first->source ?: 'Income'),
                    'icon' => $first->category ? $first->category->icon : 'bi-cash-coin',
                    'color' => $first->category ? $first->category->color : '#10b981',
                    'note' => $first->note,
                    'payment_method' => 'Income',
                    'transport_mode' => null,
                    'vehicle_type' => null,
                    'amount' => (float)$first->amount,
                ];
            })->values();

            $transactions = $transactions->merge($mappedIncomes);
        }

        // 3. Fetch Loan Settlements / Debt Payments (Balance Sheet Movements)
        if ($type !== 'expense' && $type !== 'income' && empty($categoryId)) {
            $paymentQuery = LoanPayment::with('loan')->where('user_id', $userId);

            if ($search) {
                $paymentQuery->where(function ($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%")
                      ->orWhereHas('loan', function ($lq) use ($search) {
                          $lq->where('name', 'like', "%{$search}%")
                             ->orWhere('person_name', 'like', "%{$search}%");
                      });
                });
            }
            if ($startDate) {
                $paymentQuery->whereDate('payment_date', '>=', $startDate);
            }
            if ($endDate) {
                $paymentQuery->whereDate('payment_date', '<=', $endDate);
            }
            if ($minAmount) {
                $paymentQuery->where('amount', '>=', $minAmount);
            }
            if ($maxAmount) {
                $paymentQuery->where('amount', '<=', $maxAmount);
            }

            $mappedPayments = $paymentQuery->get()->map(function ($item) {
                $isLent = $item->loan && $item->loan->isLent();
                return [
                    'id' => $item->id,
                    'type' => 'settlement',
                    'is_settlement' => true,
                    'settlement_direction' => $isLent ? 'inflow' : 'outflow',
                    'date' => $item->payment_date,
                    'category' => $isLent ? 'Loan Collection (Asset Recovery)' : 'Debt Repayment (Payable Cleared)',
                    'icon' => $isLent ? 'bi-arrow-down-left-circle-fill' : 'bi-arrow-up-right-circle-fill',
                    'color' => $isLent ? '#0284c7' : '#f59e0b',
                    'note' => ($item->loan ? ($isLent ? "Collected from {$item->loan->person_name}" : "Paid to {$item->loan->person_name}") : '') . ($item->note ? " &bull; {$item->note}" : ''),
                    'payment_method' => 'Loan Settlement',
                    'amount' => (float)$item->amount,
                ];
            });

            $transactions = $transactions->merge($mappedPayments);
        }

        // Sort descending by date
        $sortedTransactions = $transactions->sortByDesc(function ($tx) {
            return $tx['date']->timestamp * 1000 + $tx['id'];
        })->values();

        // Paginate manually for collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentItems = $sortedTransactions->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedTransactions = new LengthAwarePaginator(
            $currentItems,
            $sortedTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $categories = Category::forUser($userId)->with('parent')->orderBy('type')->orderBy('name')->get();

        return view('transactions.index', [
            'transactions' => $paginatedTransactions,
            'categories' => $categories,
            'totalCount' => $sortedTransactions->count(),
            'totalExpenseSum' => $sortedTransactions->where('type', 'expense')->sum('amount'),
            'totalIncomeSum' => $sortedTransactions->where('type', 'income')->sum('amount'),
            'totalSettlementInflow' => $sortedTransactions->where('type', 'settlement')->where('settlement_direction', 'inflow')->sum('amount'),
            'totalSettlementOutflow' => $sortedTransactions->where('type', 'settlement')->where('settlement_direction', 'outflow')->sum('amount'),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $userId = Auth::id();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $expenseQuery = Expense::with('category')->where('user_id', $userId);
        $incomeQuery = Income::with('category.parent')->where('user_id', $userId);

        if ($startDate) {
            $expenseQuery->whereDate('date', '>=', $startDate);
            $incomeQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $expenseQuery->whereDate('date', '<=', $endDate);
            $incomeQuery->whereDate('date', '<=', $endDate);
        }

        $expenses = $expenseQuery->orderBy('date', 'desc')->get();
        $incomes = $incomeQuery->orderBy('date', 'desc')->get();

        $filename = 'transactions_export_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($expenses, $incomes) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Date', 'Type', 'Category / Source', 'Amount (LKR)', 'Payment Method', 'Transport Mode', 'Vehicle Type', 'Note']);

            foreach ($incomes as $inc) {
                fputcsv($file, [
                    $inc->date->format('Y-m-d'),
                    'Income',
                    $inc->category ? $inc->category->display_name : ($inc->source ?: 'Income'),
                    number_format($inc->amount, 2, '.', ''),
                    'Income',
                    '',
                    '',
                    $inc->note ?? '',
                ]);
            }

            foreach ($expenses as $exp) {
                fputcsv($file, [
                    $exp->date->format('Y-m-d'),
                    'Expense',
                    $exp->category ? $exp->category->name : 'Uncategorized',
                    number_format($exp->amount, 2, '.', ''),
                    $exp->payment_method,
                    $exp->transport_mode ?? '',
                    $exp->vehicle_type ?? '',
                    $exp->note ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
