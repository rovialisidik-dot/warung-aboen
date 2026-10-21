<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\CashTransaction;
use App\Models\CapitalTransaction;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, monthly, yearly, custom
        $today = Carbon::today();

        $selectedDate = $request->get('date', $today->toDateString());
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        if ($period === 'daily') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodTitle = 'Harian: ' . $startDate->translatedFormat('l, d F Y');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
            $periodTitle = 'Tahunan: Tahun ' . $selectedYear;
        } elseif ($period === 'custom') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
            $periodTitle = 'Rentang Tanggal: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
        } else {
            // monthly (default)
            $period = 'monthly';
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $periodTitle = 'Bulanan: ' . $startDate->translatedFormat('F Y');
        }

        $salesQuery = Sale::completed()->whereBetween('created_at', [$startDate, $endDate]);

        $totalSales = (clone $salesQuery)->sum('total_amount');
        $totalCost = (clone $salesQuery)->sum('total_cost');
        $grossProfit = (clone $salesQuery)->sum('gross_profit');
        $totalOrders = (clone $salesQuery)->count();
        $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $profitMargin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

        // Payment method breakdown
        $paymentMethods = (clone $salesQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Chart Data & Breakdown Table based on period
        $chartLabels = [];
        $chartSales = [];
        $chartProfits = [];
        $breakdownData = [];
        $salesList = [];

        if ($period === 'daily') {
            // Breakdown per hour (06:00 - 22:00)
            $hourlySales = (clone $salesQuery)
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('SUM(gross_profit) as gross_profit'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->pluck('total_amount', 'hour')
                ->all();

            $hourlyProfit = (clone $salesQuery)
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(gross_profit) as gross_profit'))
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->pluck('gross_profit', 'hour')
                ->all();

            for ($h = 6; $h <= 22; $h++) {
                $chartLabels[] = sprintf('%02d:00', $h);
                $chartSales[] = (float) ($hourlySales[$h] ?? 0);
                $chartProfits[] = (float) ($hourlyProfit[$h] ?? 0);
            }

            // Sales list of that specific day
            $salesList = (clone $salesQuery)->with(['user', 'items'])->latest()->get();

        } elseif ($period === 'yearly') {
            // Breakdown per month (Jan - Dec)
            $monthlySales = (clone $salesQuery)
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('SUM(total_cost) as total_cost'), DB::raw('SUM(gross_profit) as gross_profit'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get()
                ->keyBy('month');

            for ($m = 1; $m <= 12; $m++) {
                $mName = Carbon::createFromDate($selectedYear, $m, 1)->translatedFormat('M');
                $chartLabels[] = $mName;
                $row = $monthlySales->get($m);
                $saleVal = $row ? (float) $row->total_amount : 0;
                $profVal = $row ? (float) $row->gross_profit : 0;
                $costVal = $row ? (float) $row->total_cost : 0;
                $countVal = $row ? (int) $row->count : 0;

                $chartSales[] = $saleVal;
                $chartProfits[] = $profVal;

                $breakdownData[] = [
                    'label' => Carbon::createFromDate($selectedYear, $m, 1)->translatedFormat('F Y'),
                    'count' => $countVal,
                    'sales' => $saleVal,
                    'cost' => $costVal,
                    'profit' => $profVal,
                    'margin' => $saleVal > 0 ? round(($profVal / $saleVal) * 100, 1) : 0,
                ];
            }
        } else {
            // Monthly or Custom: Daily breakdown
            $dailySales = (clone $salesQuery)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_amount'),
                    DB::raw('SUM(total_cost) as total_cost'),
                    DB::raw('SUM(gross_profit) as gross_profit')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            foreach ($dailySales as $day) {
                $chartLabels[] = Carbon::parse($day->date)->translatedFormat('d M');
                $chartSales[] = (float) $day->total_amount;
                $chartProfits[] = (float) $day->gross_profit;

                $breakdownData[] = [
                    'label' => Carbon::parse($day->date)->translatedFormat('l, d F Y'),
                    'count' => $day->total_orders,
                    'sales' => (float) $day->total_amount,
                    'cost' => (float) $day->total_cost,
                    'profit' => (float) $day->gross_profit,
                    'margin' => $day->total_amount > 0 ? round(($day->gross_profit / $day->total_amount) * 100, 1) : 0,
                ];
            }
        }

        // Top 10 selling products in this period
        $topProducts = SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->completed()->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('product_name', 'unit', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'), DB::raw('SUM(profit) as total_profit'))
            ->groupBy('product_name', 'unit')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        $availableYears = range(Carbon::now()->year - 3, Carbon::now()->year + 1);

        return view('reports.sales', compact(
            'period',
            'periodTitle',
            'selectedDate',
            'selectedMonth',
            'selectedYear',
            'availableYears',
            'startDate',
            'endDate',
            'totalSales',
            'totalCost',
            'grossProfit',
            'profitMargin',
            'totalOrders',
            'averageOrder',
            'paymentMethods',
            'chartLabels',
            'chartSales',
            'chartProfits',
            'breakdownData',
            'salesList',
            'topProducts'
        ));
    }

    public function profitLoss(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, monthly, yearly
        $today = Carbon::today();

        $selectedDate = $request->get('date', $today->toDateString());
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        if ($period === 'daily') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodTitle = 'Laba Rugi Harian: ' . $startDate->translatedFormat('l, d F Y');
            $expenseStart = $startDate->toDateString();
            $expenseEnd = $endDate->toDateString();
        } elseif ($period === 'yearly') {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
            $periodTitle = 'Laba Rugi Tahunan: Tahun ' . $selectedYear;
            $expenseStart = $startDate->toDateString();
            $expenseEnd = $endDate->toDateString();
        } else {
            $period = 'monthly';
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $periodTitle = 'Laba Rugi Bulanan: ' . $startDate->translatedFormat('F Y');
            $expenseStart = $startDate->toDateString();
            $expenseEnd = $endDate->toDateString();
        }

        // Sales Revenue
        $grossSales = Sale::completed()->whereBetween('created_at', [$startDate, $endDate])->sum('subtotal');
        $discounts = Sale::completed()->whereBetween('created_at', [$startDate, $endDate])->sum('discount');
        $netSales = max(0, $grossSales - $discounts);

        // COGS (HPP)
        $cogs = Sale::completed()->whereBetween('created_at', [$startDate, $endDate])->sum('total_cost');

        // Gross Profit
        $grossProfit = $netSales - $cogs;
        $grossMarginPercent = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

        // Operating Expenses
        $expensesByCategory = Expense::whereBetween('expense_date', [$expenseStart, $expenseEnd])
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get();

        $totalOperatingExpenses = $expensesByCategory->sum('total_amount');

        // Net Operating Profit
        $netProfit = $grossProfit - $totalOperatingExpenses;
        $netMarginPercent = $netSales > 0 ? ($netProfit / $netSales) * 100 : 0;

        // Monthly Comparative Matrix for Yearly Mode
        $yearlyMatrix = [];
        if ($period === 'yearly') {
            for ($m = 1; $m <= 12; $m++) {
                $mStart = Carbon::createFromDate($selectedYear, $m, 1)->startOfMonth();
                $mEnd = Carbon::createFromDate($selectedYear, $m, 1)->endOfMonth();

                $mSales = Sale::completed()->whereBetween('created_at', [$mStart, $mEnd])->sum('total_amount');
                $mCogs = Sale::completed()->whereBetween('created_at', [$mStart, $mEnd])->sum('total_cost');
                $mGross = $mSales - $mCogs;
                $mExp = Expense::whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('amount');
                $mNet = $mGross - $mExp;

                $yearlyMatrix[] = [
                    'month_num' => $m,
                    'month_name' => $mStart->translatedFormat('F'),
                    'sales' => (float) $mSales,
                    'cogs' => (float) $mCogs,
                    'gross' => (float) $mGross,
                    'expenses' => (float) $mExp,
                    'net' => (float) $mNet,
                ];
            }
        }

        $availableYears = range(Carbon::now()->year - 3, Carbon::now()->year + 1);

        return view('reports.profit_loss', compact(
            'period',
            'periodTitle',
            'selectedDate',
            'selectedMonth',
            'selectedYear',
            'availableYears',
            'grossSales',
            'discounts',
            'netSales',
            'cogs',
            'grossProfit',
            'grossMarginPercent',
            'expensesByCategory',
            'totalOperatingExpenses',
            'netProfit',
            'netMarginPercent',
            'yearlyMatrix'
        ));
    }

    public function cashFlow(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, monthly, yearly
        $today = Carbon::today();

        $selectedDate = $request->get('date', $today->toDateString());
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        if ($period === 'daily') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodTitle = 'Arus Kas Harian: ' . $startDate->translatedFormat('l, d F Y');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
            $periodTitle = 'Arus Kas Tahunan: Tahun ' . $selectedYear;
        } else {
            $period = 'monthly';
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $periodTitle = 'Arus Kas Bulanan: ' . $startDate->translatedFormat('F Y');
        }

        // Cash In
        $cashFromSales = CashTransaction::where('type', 'in')->where('source', 'sale')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashFromCapital = CashTransaction::where('type', 'in')->where('source', 'capital_in')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashOtherIn = CashTransaction::where('type', 'in')->whereNotIn('source', ['sale', 'capital_in'])->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $totalCashIn = $cashFromSales + $cashFromCapital + $cashOtherIn;

        // Cash Out
        $cashForPurchases = CashTransaction::where('type', 'out')->where('source', 'purchase')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashForExpenses = CashTransaction::where('type', 'out')->where('source', 'expense')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashForPrive = CashTransaction::where('type', 'out')->where('source', 'capital_out')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashOtherOut = CashTransaction::where('type', 'out')->whereNotIn('source', ['purchase', 'expense', 'capital_out'])->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $totalCashOut = $cashForPurchases + $cashForExpenses + $cashForPrive + $cashOtherOut;

        $netCashChange = $totalCashIn - $totalCashOut;
        $currentBalance = CashTransaction::getCurrentBalance();

        // 12-Month Matrix for Yearly Cash Flow
        $yearlyCashMatrix = [];
        if ($period === 'yearly') {
            for ($m = 1; $m <= 12; $m++) {
                $mStart = Carbon::createFromDate($selectedYear, $m, 1)->startOfMonth();
                $mEnd = Carbon::createFromDate($selectedYear, $m, 1)->endOfMonth();

                $in = CashTransaction::where('type', 'in')->whereBetween('transaction_date', [$mStart, $mEnd])->sum('amount');
                $out = CashTransaction::where('type', 'out')->whereBetween('transaction_date', [$mStart, $mEnd])->sum('amount');

                $yearlyCashMatrix[] = [
                    'month_name' => $mStart->translatedFormat('F'),
                    'cash_in' => (float) $in,
                    'cash_out' => (float) $out,
                    'net' => (float) ($in - $out),
                ];
            }
        }

        $availableYears = range(Carbon::now()->year - 3, Carbon::now()->year + 1);

        return view('reports.cash_flow', compact(
            'period',
            'periodTitle',
            'selectedDate',
            'selectedMonth',
            'selectedYear',
            'availableYears',
            'cashFromSales',
            'cashFromCapital',
            'cashOtherIn',
            'totalCashIn',
            'cashForPurchases',
            'cashForExpenses',
            'cashForPrive',
            'cashOtherOut',
            'totalCashOut',
            'netCashChange',
            'currentBalance',
            'yearlyCashMatrix'
        ));
    }

    public function stock(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->orderBy('stock', 'asc')->get();

        $totalStockItems = $products->sum('stock');
        $totalAssetCost = $products->sum(function ($p) {
            return $p->stock * $p->purchase_price;
        });
        $totalAssetSelling = $products->sum(function ($p) {
            return $p->stock * $p->selling_price;
        });
        $potentialProfit = $totalAssetSelling - $totalAssetCost;

        return view('reports.stock', compact('products', 'totalStockItems', 'totalAssetCost', 'totalAssetSelling', 'potentialProfit'));
    }

    public function exportSales(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $today = Carbon::today();
        $selectedDate = $request->get('date', $today->toDateString());
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        if ($period === 'daily') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodTitle = 'Harian (' . $startDate->translatedFormat('d F Y') . ')';
            $fileSuffix = $startDate->format('Y-m-d');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
            $periodTitle = 'Tahunan (Tahun ' . $selectedYear . ')';
            $fileSuffix = 'Tahun_' . $selectedYear;
        } elseif ($period === 'custom') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
            $periodTitle = 'Periode ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
            $fileSuffix = $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
        } else {
            $period = 'monthly';
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $periodTitle = 'Bulanan (' . $startDate->translatedFormat('F Y') . ')';
            $fileSuffix = $startDate->format('Y-m');
        }

        $sales = Sale::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'items'])
            ->orderBy('created_at', 'asc')
            ->get();

        $fileName = 'Laporan_Penjualan_' . $fileSuffix . '_' . date('His') . '.csv';
        $isOwner = auth()->user()->isOwner();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($sales, $periodTitle, $isOwner) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($f, [app_name()]);
            fputcsv($f, ['Alamat: ' . app_setting('store_address', 'Kp Cigarunggang RT 27 RW 13, Desa Sumberjaya, Kec. Cihaurbeuti, Kab. Ciamis, Jawa Barat')]);
            fputcsv($f, ['No. Telepon: ' . app_setting('store_phone', '082262438494')]);
            fputcsv($f, ['LAPORAN PENJUALAN']);
            fputcsv($f, ['Periode:', $periodTitle]);
            fputcsv($f, ['Waktu Ekspor:', date('d/m/Y H:i:s') . ' WIB']);
            fputcsv($f, ['Diekspor Oleh:', auth()->user()->name . ' (' . auth()->user()->role . ')']);
            fputcsv($f, []);

            $cols = [
                'No',
                'No. Nota / Faktur',
                'Tanggal',
                'Jam',
                'Kasir',
                'Pelanggan',
                'Metode Bayar',
                'Total Qty',
                'Subtotal (Rp)',
                'Diskon (Rp)',
                'Total Penjualan (Rp)'
            ];

            if ($isOwner) {
                $cols[] = 'HPP Modal (Rp)';
                $cols[] = 'Laba Kotor (Rp)';
                $cols[] = 'Margin (%)';
            }

            fputcsv($f, $cols);

            $totQty = 0;
            $totSub = 0;
            $totDisc = 0;
            $totGrand = 0;
            $totCost = 0;
            $totProfit = 0;

            foreach ($sales as $idx => $s) {
                $qty = $s->items->sum('quantity');
                $totQty += $qty;
                $totSub += $s->subtotal;
                $totDisc += $s->discount;
                $totGrand += $s->total_amount;
                $totCost += $s->total_cost;
                $totProfit += $s->gross_profit;

                $row = [
                    $idx + 1,
                    $s->invoice_number,
                    $s->created_at->format('d/m/Y'),
                    $s->created_at->format('H:i:s'),
                    $s->user->name ?? 'Kasir',
                    $s->customer_name,
                    strtoupper($s->payment_method),
                    $qty,
                    $s->subtotal,
                    $s->discount,
                    $s->total_amount
                ];

                if ($isOwner) {
                    $margin = $s->total_amount > 0 ? round(($s->gross_profit / $s->total_amount) * 100, 2) : 0;
                    $row[] = $s->total_cost;
                    $row[] = $s->gross_profit;
                    $row[] = $margin . '%';
                }

                fputcsv($f, $row);
            }

            fputcsv($f, []);
            $totMargin = $totGrand > 0 ? round(($totProfit / $totGrand) * 100, 2) : 0;
            $summaryRow = [
                'TOTAL',
                $sales->count() . ' Transaksi',
                '',
                '',
                '',
                '',
                '',
                $totQty,
                $totSub,
                $totDisc,
                $totGrand
            ];

            if ($isOwner) {
                $summaryRow[] = $totCost;
                $summaryRow[] = $totProfit;
                $summaryRow[] = $totMargin . '%';
            }

            fputcsv($f, $summaryRow);

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportProfitLoss(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $today = Carbon::today();
        $selectedDate = $request->get('date', $today->toDateString());
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        if ($period === 'daily') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodTitle = 'Harian: ' . $startDate->translatedFormat('d F Y');
            $expenseStart = $startDate->toDateString();
            $expenseEnd = $endDate->toDateString();
            $fileSuffix = $startDate->format('Y-m-d');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
            $periodTitle = 'Tahunan: Tahun ' . $selectedYear;
            $expenseStart = $startDate->toDateString();
            $expenseEnd = $endDate->toDateString();
            $fileSuffix = 'Tahun_' . $selectedYear;
        } else {
            $period = 'monthly';
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $periodTitle = 'Bulanan: ' . $startDate->translatedFormat('F Y');
            $expenseStart = $startDate->toDateString();
            $expenseEnd = $endDate->toDateString();
            $fileSuffix = $startDate->format('Y-m');
        }

        $grossSales = Sale::completed()->whereBetween('created_at', [$startDate, $endDate])->sum('subtotal');
        $discounts = Sale::completed()->whereBetween('created_at', [$startDate, $endDate])->sum('discount');
        $netSales = max(0, $grossSales - $discounts);
        $cogs = Sale::completed()->whereBetween('created_at', [$startDate, $endDate])->sum('total_cost');
        $grossProfit = $netSales - $cogs;
        $grossMarginPercent = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0;

        $expensesByCategory = Expense::whereBetween('expense_date', [$expenseStart, $expenseEnd])
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get();
        $totalExpenses = $expensesByCategory->sum('total_amount');
        $netProfit = $grossProfit - $totalExpenses;
        $netMarginPercent = $netSales > 0 ? round(($netProfit / $netSales) * 100, 2) : 0;

        $fileName = 'Laporan_Laba_Rugi_' . $fileSuffix . '_' . date('His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use (
            $periodTitle, $grossSales, $discounts, $netSales, $cogs,
            $grossProfit, $grossMarginPercent, $expensesByCategory,
            $totalExpenses, $netProfit, $netMarginPercent, $period, $selectedYear
        ) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($f, [app_name()]);
            fputcsv($f, ['Alamat: ' . app_setting('store_address')]);
            fputcsv($f, ['No. Telepon: ' . app_setting('store_phone')]);
            fputcsv($f, ['LAPORAN LABA RUGI (PROFIT & LOSS STATEMENT)']);
            fputcsv($f, ['Periode:', $periodTitle]);
            fputcsv($f, ['Waktu Ekspor:', date('d/m/Y H:i:s') . ' WIB']);
            fputcsv($f, ['Diekspor Oleh:', auth()->user()->name . ' (' . auth()->user()->role . ')']);
            fputcsv($f, []);

            fputcsv($f, ['KOMPONEN KEUANGAN', 'NOMINAL (RP)', 'PERSENTASE (%)']);
            fputcsv($f, ['1. PENDAPATAN PENJUALAN', '', '']);
            fputcsv($f, ['   - Penjualan Kotor (Gross Sales)', $grossSales, '']);
            fputcsv($f, ['   - Potongan / Diskon Penjualan', '-' . $discounts, '']);
            fputcsv($f, ['   TOTAL PENJUALAN BERSIH', $netSales, '100.00%']);
            fputcsv($f, []);

            fputcsv($f, ['2. HARGA POKOK PENJUALAN (HPP)', '', '']);
            fputcsv($f, ['   - Total Beban Pokok Barang Terjual', '-' . $cogs, '']);
            fputcsv($f, []);

            fputcsv($f, ['3. LABA KOTOR (GROSS PROFIT)', $grossProfit, $grossMarginPercent . '%']);
            fputcsv($f, []);

            fputcsv($f, ['4. BEBAN OPERASIONAL (EXPENSES)', '', '']);
            foreach ($expensesByCategory as $exp) {
                $catPct = $netSales > 0 ? round(($exp->total_amount / $netSales) * 100, 2) : 0;
                fputcsv($f, ['   - ' . $exp->category, '-' . $exp->total_amount, $catPct . '%']);
            }
            $expPct = $netSales > 0 ? round(($totalExpenses / $netSales) * 100, 2) : 0;
            fputcsv($f, ['   TOTAL BEBAN OPERASIONAL', '-' . $totalExpenses, $expPct . '%']);
            fputcsv($f, []);

            fputcsv($f, ['5. LABA BERSIH USAHA (NET OPERATING PROFIT)', $netProfit, $netMarginPercent . '%']);

            if ($period === 'yearly') {
                fputcsv($f, []);
                fputcsv($f, ['--- MATRIKS KOMPARASI 12 BULAN TAHUN ' . $selectedYear . ' ---']);
                fputcsv($f, ['Bulan', 'Penjualan Bersih (Rp)', 'HPP Modal (Rp)', 'Laba Kotor (Rp)', 'Beban Operasional (Rp)', 'Laba Bersih (Rp)', 'Net Margin (%)']);

                for ($m = 1; $m <= 12; $m++) {
                    $mStart = Carbon::createFromDate($selectedYear, $m, 1)->startOfMonth();
                    $mEnd = Carbon::createFromDate($selectedYear, $m, 1)->endOfMonth();

                    $mSales = Sale::completed()->whereBetween('created_at', [$mStart, $mEnd])->sum('total_amount');
                    $mCogs = Sale::completed()->whereBetween('created_at', [$mStart, $mEnd])->sum('total_cost');
                    $mGross = $mSales - $mCogs;
                    $mExp = Expense::whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('amount');
                    $mNet = $mGross - $mExp;
                    $mMargin = $mSales > 0 ? round(($mNet / $mSales) * 100, 2) : 0;

                    fputcsv($f, [
                        $mStart->translatedFormat('F'),
                        $mSales,
                        $mCogs,
                        $mGross,
                        $mExp,
                        $mNet,
                        $mMargin . '%'
                    ]);
                }
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCashFlow(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $today = Carbon::today();
        $selectedDate = $request->get('date', $today->toDateString());
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedYear = (int) $request->get('year', Carbon::now()->year);

        if ($period === 'daily') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodTitle = 'Harian: ' . $startDate->translatedFormat('d F Y');
            $fileSuffix = $startDate->format('Y-m-d');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear();
            $periodTitle = 'Tahunan: Tahun ' . $selectedYear;
            $fileSuffix = 'Tahun_' . $selectedYear;
        } else {
            $period = 'monthly';
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $periodTitle = 'Bulanan: ' . $startDate->translatedFormat('F Y');
            $fileSuffix = $startDate->format('Y-m');
        }

        $cashFromSales = CashTransaction::where('type', 'in')->where('source', 'sale')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashFromCapital = CashTransaction::where('type', 'in')->where('source', 'capital_in')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashOtherIn = CashTransaction::where('type', 'in')->whereNotIn('source', ['sale', 'capital_in'])->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $totalCashIn = $cashFromSales + $cashFromCapital + $cashOtherIn;

        $cashForPurchases = CashTransaction::where('type', 'out')->where('source', 'purchase')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashForExpenses = CashTransaction::where('type', 'out')->where('source', 'expense')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashForPrive = CashTransaction::where('type', 'out')->where('source', 'capital_out')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $cashOtherOut = CashTransaction::where('type', 'out')->whereNotIn('source', ['purchase', 'expense', 'capital_out'])->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
        $totalCashOut = $cashForPurchases + $cashForExpenses + $cashForPrive + $cashOtherOut;

        $netCashChange = $totalCashIn - $totalCashOut;
        $currentBalance = CashTransaction::getCurrentBalance();

        $fileName = 'Laporan_Arus_Kas_' . $fileSuffix . '_' . date('His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use (
            $periodTitle, $cashFromSales, $cashFromCapital, $cashOtherIn, $totalCashIn,
            $cashForPurchases, $cashForExpenses, $cashForPrive, $cashOtherOut, $totalCashOut,
            $netCashChange, $currentBalance, $period, $selectedYear
        ) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($f, [app_name()]);
            fputcsv($f, ['Alamat: ' . app_setting('store_address')]);
            fputcsv($f, ['No. Telepon: ' . app_setting('store_phone')]);
            fputcsv($f, ['LAPORAN ARUS KAS (STATEMENT OF CASH FLOWS)']);
            fputcsv($f, ['Periode:', $periodTitle]);
            fputcsv($f, ['Waktu Ekspor:', date('d/m/Y H:i:s') . ' WIB']);
            fputcsv($f, ['Diekspor Oleh:', auth()->user()->name . ' (' . auth()->user()->role . ')']);
            fputcsv($f, []);

            fputcsv($f, ['AKTIVITAS KAS', 'NOMINAL (RP)']);
            fputcsv($f, ['1. ARUS KAS MASUK (CASH INFLOW)', '']);
            fputcsv($f, ['   - Penerimaan dari Penjualan Dagang', $cashFromSales]);
            fputcsv($f, ['   - Setoran Tambahan Modal Usaha', $cashFromCapital]);
            fputcsv($f, ['   - Penerimaan Kas Lain-lain', $cashOtherIn]);
            fputcsv($f, ['   TOTAL ARUS KAS MASUK', $totalCashIn]);
            fputcsv($f, []);

            fputcsv($f, ['2. ARUS KAS KELUAR (CASH OUTFLOW)', '']);
            fputcsv($f, ['   - Pengadaan / Pembelian Stok Barang', '-' . $cashForPurchases]);
            fputcsv($f, ['   - Pembayaran Beban Operasional Warung', '-' . $cashForExpenses]);
            fputcsv($f, ['   - Penarikan Keperluan Pemilik (Prive)', '-' . $cashForPrive]);
            fputcsv($f, ['   - Pengeluaran Kas Lain-lain', '-' . $cashOtherOut]);
            fputcsv($f, ['   TOTAL ARUS KAS KELUAR', '-' . $totalCashOut]);
            fputcsv($f, []);

            fputcsv($f, ['3. SURPLUS / (DEFISIT) ARUS KAS BERSIH', $netCashChange]);
            fputcsv($f, ['4. TOTAL SALDO KAS SAAT INI', $currentBalance]);

            if ($period === 'yearly') {
                fputcsv($f, []);
                fputcsv($f, ['--- KOMPARASI ARUS KAS 12 BULAN TAHUN ' . $selectedYear . ' ---']);
                fputcsv($f, ['Bulan', 'Kas Masuk (Rp)', 'Kas Keluar (Rp)', 'Arus Kas Bersih (Rp)']);

                for ($m = 1; $m <= 12; $m++) {
                    $mStart = Carbon::createFromDate($selectedYear, $m, 1)->startOfMonth();
                    $mEnd = Carbon::createFromDate($selectedYear, $m, 1)->endOfMonth();

                    $in = CashTransaction::where('type', 'in')->whereBetween('transaction_date', [$mStart, $mEnd])->sum('amount');
                    $out = CashTransaction::where('type', 'out')->whereBetween('transaction_date', [$mStart, $mEnd])->sum('amount');
                    $net = $in - $out;

                    fputcsv($f, [
                        $mStart->translatedFormat('F'),
                        $in,
                        $out,
                        $net
                    ]);
                }
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportStock(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->orderBy('name', 'asc')->get();

        $fileName = 'Laporan_Persediaan_Stok_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products, $request) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($f, [app_name()]);
            fputcsv($f, ['Alamat: ' . app_setting('store_address')]);
            fputcsv($f, ['No. Telepon: ' . app_setting('store_phone')]);
            fputcsv($f, ['LAPORAN PERSEDIAAN BARANG & VALUASI STOK']);
            fputcsv($f, ['Kategori Filter:', $request->boolean('low_stock_only') ? 'Hanya Stok Menipis / Kritis' : 'Seluruh Barang Aktif']);
            fputcsv($f, ['Waktu Ekspor:', date('d/m/Y H:i:s') . ' WIB']);
            fputcsv($f, ['Diekspor Oleh:', auth()->user()->name . ' (' . auth()->user()->role . ')']);
            fputcsv($f, []);

            $cols = [
                'No',
                'Barcode / SKU',
                'Nama Produk',
                'Kategori',
                'Satuan',
                'Stok Fisik',
                'Stok Minimum',
                'Status Persediaan',
                'Harga Beli Pokok (HPP)',
                'Harga Jual',
                'Nilai Aset Modal (Rp)',
                'Potensi Nilai Jual (Rp)',
                'Potensi Keuntungan (Rp)',
                'Margin (%)'
            ];
            fputcsv($f, $cols);

            $totQty = 0;
            $totAssetCost = 0;
            $totAssetSell = 0;
            $totPotentialProfit = 0;

            foreach ($products as $idx => $p) {
                $assetCost = $p->stock * $p->purchase_price;
                $assetSell = $p->stock * $p->selling_price;
                $profit = $assetSell - $assetCost;
                $margin = $p->selling_price > 0 ? round((($p->selling_price - $p->purchase_price) / $p->selling_price) * 100, 2) : 0;

                if ($p->stock <= 0) {
                    $status = 'Habis';
                } elseif ($p->stock <= $p->min_stock) {
                    $status = 'Menipis';
                } else {
                    $status = 'Aman';
                }

                $totQty += $p->stock;
                $totAssetCost += $assetCost;
                $totAssetSell += $assetSell;
                $totPotentialProfit += $profit;

                fputcsv($f, [
                    $idx + 1,
                    "'" . $p->barcode,
                    $p->name,
                    $p->category->name ?? '-',
                    $p->unit,
                    $p->stock,
                    $p->min_stock,
                    $status,
                    $p->purchase_price,
                    $p->selling_price,
                    $assetCost,
                    $assetSell,
                    $profit,
                    $margin . '%'
                ]);
            }

            fputcsv($f, []);
            $overallMargin = $totAssetSell > 0 ? round(($totPotentialProfit / $totAssetSell) * 100, 2) : 0;
            fputcsv($f, [
                'TOTAL KESELURUHAN',
                '',
                $products->count() . ' Jenis SKU',
                '',
                '',
                $totQty,
                '',
                '',
                '',
                '',
                $totAssetCost,
                $totAssetSell,
                $totPotentialProfit,
                $overallMargin . '%'
            ]);

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
