<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceApiController extends Controller
{
    /**
     * Memeriksa apakah user yang diautentikasi memiliki peranan Finance/Admin
     */
    private function checkFinancePermission(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['finance', 'webmaster', 'administrator', 'admin'])) {
            return false;
        }
        return true;
    }

    /**
     * GET /api/v1/finance/accounts - Daftar Kode Akun (COA)
     */
    public function accounts(Request $request)
    {
        $accounts = Account::where('status', 'Aktif')
            ->orderBy('code', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $accounts->count(),
            'data' => $accounts
        ]);
    }

    /**
     * GET /api/v1/finance/journals - Daftar Jurnal Keuangan & Transaksi Kas
     */
    public function index(Request $request)
    {
        $query = JournalEntry::with(['items.account']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $entries = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $entries->count(),
            'data' => $entries
        ]);
    }

    /**
     * GET /api/v1/finance/journals/{id} - Detail Jurnal Keuangan
     */
    public function show($id)
    {
        $entry = JournalEntry::with(['items.account'])->find($id);

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Data jurnal tidak ditemukan.'
            ], 44);
        }

        return response()->json([
            'success' => true,
            'data' => $entry
        ]);
    }

    /**
     * POST /api/v1/finance/journals - Tambah Jurnal Keuangan Baru (Khusus Role Finance)
     */
    public function store(Request $request)
    {
        if (!$this->checkFinancePermission($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur CRUD Jurnal Keuangan khusus untuk peranan Finance & Keuangan MKT.'
            ], 403);
        }

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
                $debitSum += (float) $item['amount'];
            } else {
                $creditSum += (float) $item['amount'];
            }
        }

        if (abs($debitSum - $creditSum) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal Tidak Balance: Total Debit (Rp ' . number_format($debitSum) . ') harus sama dengan Total Credit (Rp ' . number_format($creditSum) . ').'
            ], 422);
        }

        $entry = DB::transaction(function () use ($request, $items) {
            $ref = $request->input('reference_number');
            if (!$ref || trim($ref) === '') {
                $ref = 'JK-' . date('Ymd') . '-' . rand(100, 999);
            }

            $journal = JournalEntry::create([
                'entry_date' => $request->input('entry_date'),
                'description' => $request->input('description'),
                'reference_number' => $ref,
            ]);

            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $item['account_id'],
                    'type' => $item['type'],
                    'amount' => $item['amount'],
                ]);
            }

            return $journal->load(['items.account']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Jurnal transaksi keuangan berhasil disimpan.',
            'data' => $entry
        ], 201);
    }

    /**
     * PUT /api/v1/finance/journals/{id} - Edit Jurnal Keuangan (Khusus Role Finance)
     */
    public function update(Request $request, $id)
    {
        if (!$this->checkFinancePermission($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur CRUD Jurnal Keuangan khusus untuk peranan Finance & Keuangan MKT.'
            ], 403);
        }

        $entry = JournalEntry::find($id);
        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Data jurnal tidak ditemukan.'
            ], 404);
        }

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
                $debitSum += (float) $item['amount'];
            } else {
                $creditSum += (float) $item['amount'];
            }
        }

        if (abs($debitSum - $creditSum) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal Tidak Balance: Total Debit harus sama dengan Total Credit.'
            ], 422);
        }

        $updatedEntry = DB::transaction(function () use ($request, $entry, $items) {
            $entry->update([
                'entry_date' => $request->input('entry_date'),
                'description' => $request->input('description'),
                'reference_number' => $request->input('reference_number') ?: $entry->reference_number,
            ]);

            $entry->items()->delete();

            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $item['account_id'],
                    'type' => $item['type'],
                    'amount' => $item['amount'],
                ]);
            }

            return $entry->load(['items.account']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Jurnal transaksi keuangan berhasil diperbarui.',
            'data' => $updatedEntry
        ]);
    }

    /**
     * DELETE /api/v1/finance/journals/{id} - Hapus Jurnal Keuangan (Khusus Role Finance)
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->checkFinancePermission($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur CRUD Jurnal Keuangan khusus untuk peranan Finance & Keuangan MKT.'
            ], 403);
        }

        $entry = JournalEntry::find($id);
        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Data jurnal tidak ditemukan.'
            ], 404);
        }

        DB::transaction(function () use ($entry) {
            $entry->items()->delete();
            $entry->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Jurnal transaksi keuangan berhasil dihapus.'
        ]);
    }
}
