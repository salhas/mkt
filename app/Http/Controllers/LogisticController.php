<?php

namespace App\Http\Controllers;

use App\Models\Logistic;
use App\Models\LogisticTransaction;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogisticController extends Controller
{
    public function index(Request $request)
    {
        $query = Logistic::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $logistics = $query->orderBy('item_name', 'asc')->paginate(10)->withQueryString();

        // Transactions
        $transactionsQuery = LogisticTransaction::with('logistic');
        if ($request->filled('transaction_type')) {
            $transactionsQuery->where('type', $request->input('transaction_type'));
        }
        $transactions = $transactionsQuery->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(10, ['*'], 'transactions_page')->withQueryString();

        // Summary Statistics
        $stats = [
            'total_items' => Logistic::count(),
            'low_stock_items' => Logistic::where('quantity', '<', 10)->count(),
            'total_received' => (int) LogisticTransaction::where('type', 'Masuk')->sum('quantity'),
            'total_distributed' => (int) LogisticTransaction::where('type', 'Keluar')->sum('quantity'),
        ];

        return Inertia::render('Logistics/Index', [
            'logistics' => $logistics,
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => $request->only(['search', 'category', 'transaction_type', 'tab'])
        ]);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        Logistic::create($validated);

        return redirect()->back()->with('success', 'Barang logistik berhasil ditambahkan.');
    }

    public function updateItem(Request $request, Logistic $logistic)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $logistic->update($validated);

        return redirect()->back()->with('success', 'Barang logistik berhasil diperbarui.');
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'logistic_id' => 'required|exists:logistics,id',
            'type' => 'required|string|in:Masuk,Keluar',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'recipient_or_donor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($validated) {
            LogisticTransaction::create($validated);

            $logistic = Logistic::findOrFail($validated['logistic_id']);
            if ($validated['type'] === 'Masuk') {
                $logistic->quantity += $validated['quantity'];
            } else {
                // Prevent negative stock
                if ($logistic->quantity < $validated['quantity']) {
                    throw new \Exception('Stok logistik tidak mencukupi.');
                }
                $logistic->quantity -= $validated['quantity'];
            }
            $logistic->save();
        });

        return redirect()->back()->with('success', 'Transaksi logistik berhasil dicatat.');
    }
}
