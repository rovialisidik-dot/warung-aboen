<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Expense;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Sales Metrics
        $salesToday = Sale::completed()->whereDate('created_at', $today)->sum('total_amount');
        $salesCountToday = Sale::completed()->whereDate('created_at', $today)->count();

        $salesMonth = Sale::completed()->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->sum('total_amount');
        $grossProfitMonth = Sale::completed()->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->sum('gross_profit');

        // Expenses
        $expensesMonth = Expense::whereMonth('expense_date', $thisMonth)->whereYear('expense_date', $thisYear)->sum('amount');
        $netProfitMonth = $grossProfitMonth - $expensesMonth;

        // Cash Balance
        $cashBalance = CashTransaction::getCurrentBalance();

        // Products & Stock
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->get();
        $lowStockCount = $lowStockProducts->count();

        // 7-day sales chart data
        $chartLabels = [];
        $chartSales = [];
        $chartProfits = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayLabel = $date->translatedFormat('d M');
            $chartLabels[] = $dayLabel;

            $daySale = Sale::completed()->whereDate('created_at', $date)->sum('total_amount');
            $dayProfit = Sale::completed()->whereDate('created_at', $date)->sum('gross_profit');

            $chartSales[] = (float) $daySale;
            $chartProfits[] = (float) $dayProfit;
        }

        // Recent sales
        $recentSales = Sale::with('user')->latest()->take(6)->get();

        // Recent cash movements
        $recentCash = CashTransaction::latest()->take(5)->get();

        return view('dashboard.index', compact(
            'salesToday',
            'salesCountToday',
            'salesMonth',
            'grossProfitMonth',
            'expensesMonth',
            'netProfitMonth',
            'cashBalance',
            'totalProducts',
            'lowStockCount',
            'lowStockProducts',
            'chartLabels',
            'chartSales',
            'chartProfits',
            'recentSales',
            'recentCash'
        ));
    }
}
