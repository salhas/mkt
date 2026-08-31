<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonationAccountingService
{
    /**
     * Sinkronisasi satu transaksi donasi ke Jurnal Umum & Buku Besar
     */
    public function syncDonation(Donation $donation): ?JournalEntry
    {
        // Pastikan donasi memiliki nomor referensi unik
        if (empty($donation->reference_number)) {
            $donation->reference_number = 'DON-' . date('Ymd', strtotime($donation->donation_date ?: 'now')) . '-' . str_pad($donation->id, 4, '0', STR_PAD_LEFT);
            $donation->saveQuietly();
        }

        // Cari atau pastikan referensi jurnal menggunakan reference_number donasi
        $refNumber = $donation->reference_number;

        // Jika status BUKAN 'Sukses', hapus jurnal jika sebelumnya pernah dicatat
        if ($donation->status !== 'Sukses') {
            $this->removeDonationJournal($donation);
            return null;
        }

        return DB::transaction(function () use ($donation, $refNumber) {
            // 1. Tentukan Akun Debit (Kas / Bank - Asset)
            $debitAccount = $this->resolveDebitAccount($donation->payment_method);
            
            // 2. Tentukan Akun Kredit (Pendapatan - Revenue)
            $creditAccount = $this->resolveCreditAccount($donation);

            if (!$debitAccount || !$creditAccount) {
                Log::warning("DonationAccountingService: Gagal menemukan akun COA untuk donasi #{$donation->id}");
                return null;
            }

            // 3. Nama / Identitas Donatur untuk Keterangan Jurnal
            $donorName = $donation->donor ? $donation->donor->name : 'Hamba Allah';
            $description = "Penerimaan Donasi - {$donorName} ({$donation->payment_method})";
            if (!empty($donation->description)) {
                $description .= " - " . $donation->description;
            }

            // 4. Buat atau perbarui JournalEntry
            $entry = JournalEntry::updateOrCreate(
                ['reference_number' => $refNumber],
                [
                    'entry_date' => $donation->donation_date,
                    'description' => $description,
                ]
            );

            // 5. Bersihkan item jurnal sebelumnya dan buat yang baru (Double Entry)
            $entry->items()->delete();

            // Baris Debit: Kas / Bank (Bertambah di Debit)
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $debitAccount->id,
                'type' => 'Debit',
                'amount' => $donation->amount,
            ]);

            // Baris Kredit: Pendapatan Donasi (Bertambah di Kredit)
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $creditAccount->id,
                'type' => 'Credit',
                'amount' => $donation->amount,
            ]);

            return $entry;
        });
    }

    /**
     * Hapus jurnal yang berelasi dengan donasi
     */
    public function removeDonationJournal(Donation $donation): void
    {
        if (empty($donation->reference_number)) {
            return;
        }

        DB::transaction(function () use ($donation) {
            $entries = JournalEntry::where('reference_number', $donation->reference_number)->get();
            foreach ($entries as $entry) {
                $entry->items()->delete();
                $entry->delete();
            }
        });
    }

    /**
     * Sinkronisasi seluruh data donasi yang berstatus Sukses ke Jurnal Keuangan
     */
    public function syncAllDonations(): int
    {
        $donations = Donation::with('donor')->get();
        $syncedCount = 0;

        foreach ($donations as $donation) {
            $this->syncDonation($donation);
            if ($donation->status === 'Sukses') {
                $syncedCount++;
            }
        }

        return $syncedCount;
    }

    /**
     * Resolusi Akun Debit (Kas/Bank) berdasarkan metode pembayaran
     */
    private function resolveDebitAccount(?string $paymentMethod): ?Account
    {
        $method = strtolower($paymentMethod ?? '');

        // 1002 - Bank Mandiri
        if (str_contains($method, 'mandiri')) {
            $acc = Account::where('code', '1002')->first();
            if ($acc) return $acc;
        }

        // 1003 - Bank Syariah Indonesia (BSI)
        if (str_contains($method, 'bsi') || str_contains($method, 'syariah')) {
            $acc = Account::where('code', '1003')->first();
            if ($acc) return $acc;
        }

        // 1004 - Bank BCA
        if (str_contains($method, 'bca')) {
            $acc = Account::where('code', '1004')->first();
            if ($acc) return $acc;
        }

        // 1001 - Kas Utama (Tunai / Cash)
        if (str_contains($method, 'tunai') || str_contains($method, 'cash')) {
            $acc = Account::where('code', '1001')->first();
            if ($acc) return $acc;
        }

        // Default: Utamakan Rekening Donasi (1003 BSI), lalu Kas Utama (1001), atau akun Asset pertama
        return Account::where('code', '1003')->first()
            ?: Account::where('code', '1001')->first()
            ?: Account::where('type', 'Asset')->first();
    }

    /**
     * Resolusi Akun Kredit (Pendapatan) berdasarkan tipe donatur
     */
    private function resolveCreditAccount(Donation $donation): ?Account
    {
        $isLembaga = false;
        if ($donation->donor && strtolower($donation->donor->type) === 'lembaga') {
            $isLembaga = true;
        }

        if ($isLembaga) {
            // 4002 - Pendapatan CSR Lembaga/Corporate
            $acc = Account::where('code', '4002')->first();
            if ($acc) return $acc;
        }

        // 4001 - Pendapatan Donasi Publik
        return Account::where('code', '4001')->first()
            ?: Account::where('type', 'Revenue')->first();
    }
}
