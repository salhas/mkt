<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    private function authorizeFinanceAccess()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['webmaster', 'administrator', 'finance'])) {
            abort(403, 'Akses Ditolak: Hanya Role Finance dan Administrator yang memiliki hak akses untuk mengubah data keuangan.');
        }
    }

    // --- CHART OF ACCOUNTS (COA) / PENGATURAN KODE AKUN ---
    public function indexCoa(Request $request)
    {
        $query = Account::withCount('journalItems');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->input('type') !== 'Semua') {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        $accounts = $query->orderBy('code', 'asc')->get();

        $stats = [
            'total_accounts' => Account::count(),
            'total_asset' => Account::where('type', 'Asset')->count(),
            'total_liability' => Account::where('type', 'Liability')->count(),
            'total_equity' => Account::where('type', 'Equity')->count(),
            'total_revenue' => Account::where('type', 'Revenue')->count(),
            'total_expense' => Account::where('type', 'Expense')->count(),
        ];

        $accountTypes = ['Semua', 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'];

        return Inertia::render('Finance/Coa', [
            'accounts' => $accounts,
            'stats' => $stats,
            'accountTypes' => $accountTypes,
            'filters' => $request->only(['search', 'type', 'status'])
        ]);
    }

    public function storeAccount(Request $request)
    {
        $this->authorizeFinanceAccess();

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense',
            'normal_balance' => 'required|string|in:Debit,Credit',
            'status' => 'required|string|in:Aktif,Tidak Aktif',
            'description' => 'nullable|string',
        ]);

        Account::create($validated);

        return redirect()->back()->with('success', 'Kode Akun (COA) berhasil ditambahkan.');
    }

    public function updateAccount(Request $request, Account $account)
    {
        $this->authorizeFinanceAccess();

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense',
            'normal_balance' => 'required|string|in:Debit,Credit',
            'status' => 'required|string|in:Aktif,Tidak Aktif',
            'description' => 'nullable|string',
        ]);

        $account->update($validated);

        return redirect()->back()->with('success', 'Kode Akun (COA) berhasil diperbarui.');
    }

    public function destroyAccount(Account $account)
    {
        $this->authorizeFinanceAccess();

        if ($account->journalItems()->exists()) {
            return redirect()->back()->withErrors([
                'delete' => "Akun {$account->code} - {$account->name} tidak dapat dihapus karena telah digunakan dalam transaksi jurnal."
            ]);
        }

        $account->delete();

        return redirect()->back()->with('success', 'Kode Akun berhasil dihapus.');
    }

    // --- JOURNAL ENTRIES (JURNAL UMUM) ---
    public function indexJournal(Request $request)
    {
        $query = JournalEntry::with(['items.account']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $entries = $query->orderBy('entry_date', 'desc')->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $accounts = Account::where('status', 'Aktif')->orderBy('code')->get();

        return Inertia::render('Finance/Journal', [
            'entries' => $entries,
            'accounts' => $accounts,
            'filters' => $request->only(['search', 'start_date', 'end_date'])
        ]);
    }

    public function storeJournal(Request $request)
    {
        $this->authorizeFinanceAccess();

        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.type' => 'required|string|in:Debit,Credit',
            'items.*.amount' => 'required|numeric|min:0.01',
        ]);

        $items = $request->input('items');
        $debitSum = 0;
        $creditSum = 0;

        foreach ($items as $item) {
            if ($item['type'] === 'Debit') {
                $debitSum += $item['amount'];
            } else {
                $creditSum += $item['amount'];
            }
        }

        if (abs($debitSum - $creditSum) > 0.01) {
            return redirect()->back()->withErrors(['balance' => 'Total Debit harus sama dengan total Credit (Jurnal tidak balance).']);
        }

        DB::transaction(function() use ($request, $items) {
            $entry = JournalEntry::create([
                'entry_date' => $request->input('entry_date'),
                'description' => $request->input('description'),
                'reference_number' => $request->input('reference_number') ?: 'JE-' . date('Ymd') . '-' . rand(100, 999),
            ]);

            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $item['account_id'],
                    'type' => $item['type'],
                    'amount' => $item['amount']
                ]);
            }
        });

        return redirect()->back()->with('success', 'Jurnal berhasil disimpan.');
    }

    public function updateJournal(Request $request, JournalEntry $journalEntry)
    {
        $this->authorizeFinanceAccess();

        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.type' => 'required|string|in:Debit,Credit',
            'items.*.amount' => 'required|numeric|min:0.01',
        ]);

        $items = $request->input('items');
        $debitSum = 0;
        $creditSum = 0;

        foreach ($items as $item) {
            if ($item['type'] === 'Debit') {
                $debitSum += $item['amount'];
            } else {
                $creditSum += $item['amount'];
            }
        }

        if (abs($debitSum - $creditSum) > 0.01) {
            return redirect()->back()->withErrors(['balance' => 'Total Debit harus sama dengan total Credit (Jurnal tidak balance).']);
        }

        DB::transaction(function() use ($request, $items, $journalEntry) {
            $journalEntry->update([
                'entry_date' => $request->input('entry_date'),
                'description' => $request->input('description'),
                'reference_number' => $request->input('reference_number') ?: $journalEntry->reference_number,
            ]);

            // Remove existing items and replace with updated items
            $journalEntry->items()->delete();

            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'type' => $item['type'],
                    'amount' => $item['amount']
                ]);
            }
        });

        return redirect()->back()->with('success', 'Jurnal transaksi berhasil diperbarui.');
    }

    public function destroyJournal(JournalEntry $journalEntry)
    {
        $this->authorizeFinanceAccess();

        DB::transaction(function() use ($journalEntry) {
            $journalEntry->items()->delete();
            $journalEntry->delete();
        });

        return redirect()->back()->with('success', 'Jurnal transaksi berhasil dihapus.');
    }

    // --- BUKU BESAR (GENERAL LEDGER) ---
    public function indexLedger(Request $request)
    {
        $accounts = Account::orderBy('code')->get();
        $selectedAccountId = $request->input('account_id', $accounts->first() ? $accounts->first()->id : null);

        $ledgerItems = [];
        $selectedAccount = null;

        if ($selectedAccountId) {
            $selectedAccount = Account::find($selectedAccountId);
            $ledgerItems = JournalItem::where('account_id', $selectedAccountId)
                ->whereHas('entry', function($q) use ($request) {
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $q->whereBetween('entry_date', [$request->input('start_date'), $request->input('end_date')]);
                    }
                })
                ->with('entry')
                ->get()
                ->sortBy(function($item) {
                    return $item->entry->entry_date;
                })
                ->values();
        }

        return Inertia::render('Finance/Ledger', [
            'accounts' => $accounts,
            'selectedAccountId' => (int) $selectedAccountId,
            'selectedAccount' => $selectedAccount,
            'ledgerItems' => $ledgerItems,
            'filters' => $request->only(['account_id', 'start_date', 'end_date'])
        ]);
    }

    // --- NERACA (BALANCE SHEET) ---
    public function indexBalanceSheet(Request $request)
    {
        $asOfDate = $request->input('date', date('Y-m-d'));
        $accounts = Account::all();

        $assets = [];
        $liabilities = [];
        $equity = [];
        $revenue = [];
        $expense = [];

        foreach ($accounts as $account) {
            $items = JournalItem::where('account_id', $account->id)
                ->whereHas('entry', function($q) use ($asOfDate) {
                    $q->where('entry_date', '<=', $asOfDate);
                })->get();

            $debitSum = $items->where('type', 'Debit')->sum('amount');
            $creditSum = $items->where('type', 'Credit')->sum('amount');

            if ($account->type === 'Asset' || $account->type === 'Expense') {
                $balance = $debitSum - $creditSum;
            } else {
                $balance = $creditSum - $debitSum;
            }

            $account->balance = $balance;

            if ($account->type === 'Asset') {
                $assets[] = $account;
            } elseif ($account->type === 'Liability') {
                $liabilities[] = $account;
            } elseif ($account->type === 'Equity') {
                $equity[] = $account;
            } elseif ($account->type === 'Revenue') {
                $revenue[] = $account;
            } elseif ($account->type === 'Expense') {
                $expense[] = $account;
            }
        }

        $totalRevenue = collect($revenue)->sum('balance');
        $totalExpense = collect($expense)->sum('balance');
        $currentSurplus = $totalRevenue - $totalExpense;

        $surplusAccount = new Account([
            'code' => '3999',
            'name' => 'Surplus / Defisit Periode Berjalan',
            'type' => 'Equity'
        ]);
        $surplusAccount->balance = $currentSurplus;
        $equity[] = $surplusAccount;

        return Inertia::render('Finance/BalanceSheet', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'asOfDate' => $asOfDate,
            'totalAssets' => collect($assets)->sum('balance'),
            'totalLiabilities' => collect($liabilities)->sum('balance'),
            'totalEquity' => collect($equity)->sum('balance'),
        ]);
    }
}
