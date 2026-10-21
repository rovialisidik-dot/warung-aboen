<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashTransaction;
use App\Models\CapitalTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CashController extends Controller
{
    public function index(Request $request)
    {
        $query = CashTransaction::with('user')->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $currentBalance = CashTransaction::getCurrentBalance();
        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');

        // Recent capital transactions (modal & prive)
        $capitalTransactions = CapitalTransaction::with('user')->latest()->take(10)->get();

        return view('cash.index', compact('transactions', 'currentBalance', 'totalIn', 'totalOut', 'capitalTransactions'));
    }

    public function storeCapital(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:modal_masuk,penarikan_pribadi',
            'amount' => 'required|numeric|min:1000',
            'transaction_date' => 'required|date',
            'notes' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $isModal = ($validated['type'] === 'modal_masuk');
                $amount = (float) $validated['amount'];

                $cap = CapitalTransaction::create([
                    'user_id' => auth()->id(),
                    'type' => $validated['type'],
                    'amount' => $amount,
                    'transaction_date' => $validated['transaction_date'],
                    'notes' => $validated['notes'],
                ]);

                if ($isModal) {
                    CashTransaction::record(
                        'in',
                        'capital_in',
                        $amount,
                        "Setoran Modal Pemilik: {$validated['notes']}",
                        $cap->id,
                        auth()->id()
                    );
                    AuditLog::log('capital_in', "Setoran modal baru sebesar " . format_rupiah($amount) . ". Ket: {$validated['notes']}", 'CapitalTransaction', $cap->id);
                } else {
                    CashTransaction::record(
                        'out',
                        'capital_out',
                        $amount,
                        "Penarikan Pribadi (Prive): {$validated['notes']}",
                        $cap->id,
                        auth()->id()
                    );
                    AuditLog::log('capital_out', "Penarikan pribadi (Prive) sebesar " . format_rupiah($amount) . ". Ket: {$validated['notes']}", 'CapitalTransaction', $cap->id);
                }
            });

            $msg = $validated['type'] === 'modal_masuk' ? 'Setoran modal berhasil ditambahkan ke saldo kas.' : 'Penarikan pribadi (Prive) berhasil dicatat dan kas telah dipotong.';
            return redirect()->route('cash.index')->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat transaksi modal/prive: ' . $e->getMessage())->withInput();
        }
    }
}
