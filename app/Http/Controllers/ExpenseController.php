<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\CashTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user')->latest('expense_date');

        if ($request->filled('month')) {
            $parts = explode('-', $request->month);
            $query->whereYear('expense_date', $parts[0])->whereMonth('expense_date', $parts[1]);
        } else {
            $query->whereYear('expense_date', Carbon::now()->year)->whereMonth('expense_date', Carbon::now()->month);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->paginate(15)->withQueryString();
        $totalExpenses = (clone $query)->sum('amount');

        $categories = [
            'Operasional',
            'Listrik & Air',
            'Sewa Tempat',
            'Plastik & Kemasan',
            'Gaji / Upah',
            'Transportasi',
            'Pemeliharaan / Alat',
            'Lainnya',
        ];

        return view('expenses.index', compact('expenses', 'totalExpenses', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $expense = Expense::create([
                    'user_id' => auth()->id(),
                    'title' => $validated['title'],
                    'category' => $validated['category'],
                    'amount' => $validated['amount'],
                    'expense_date' => $validated['expense_date'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                CashTransaction::record(
                    'out',
                    'expense',
                    $expense->amount,
                    "Biaya {$expense->category}: {$expense->title}",
                    $expense->id,
                    auth()->id()
                );

                AuditLog::log('expense_created', "Pengeluaran operasional '{$expense->title}' senilai " . format_rupiah($expense->amount) . " dicatat.", 'Expense', $expense->id);
            });

            return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat dan kas telah dipotong.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Expense $expense)
    {
        try {
            DB::transaction(function () use ($expense) {
                $title = $expense->title;
                $amount = $expense->amount;

                // Refund to cash if deleted
                CashTransaction::record(
                    'in',
                    'adjustment',
                    $amount,
                    "Pengembalian kas karena penghapusan biaya '{$title}'",
                    $expense->id,
                    auth()->id()
                );

                $expense->delete();

                AuditLog::log('expense_deleted', "Biaya '{$title}' sebesar " . format_rupiah($amount) . " dihapus dan kas dikembalikan.", 'Expense');
            });

            return redirect()->route('expenses.index')->with('success', 'Data pengeluaran berhasil dihapus dan saldo kas telah disesuaikan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus pengeluaran: ' . $e->getMessage());
        }
    }
}
