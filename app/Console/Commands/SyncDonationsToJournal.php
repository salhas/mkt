<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DonationAccountingService;

class SyncDonationsToJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donations:sync-journals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi seluruh transaksi donasi (Sukses) ke sistem Jurnal Keuangan & Buku Besar';

    /**
     * Execute the console command.
     */
    public function handle(DonationAccountingService $service): int
    {
        $this->info('Memulai sinkronisasi transaksi donasi ke Jurnal Umum & Buku Besar...');

        $count = $service->syncAllDonations();

        $this->info("Berhasil! Sebanyak {$count} transaksi donasi telah disinkronkan ke laporan keuangan.");

        return Command::SUCCESS;
    }
}
