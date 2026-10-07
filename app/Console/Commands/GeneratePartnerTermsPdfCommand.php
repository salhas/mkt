<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PartnerTermsPdfService;

class GeneratePartnerTermsPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkt:generate-terms-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dokumen resmi PDF Syarat & Ketentuan Kemitraan berbasis profil MktProfile dari database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembuatan berkas PDF Syarat & Ketentuan Kemitraan...');

        $success = PartnerTermsPdfService::generatePdf();

        if ($success) {
            $this->info('Berkas PDF berhasil digenerate di public/docs/syarat-dan-ketentuan-kemitraan-mkt.pdf');
            return Command::SUCCESS;
        }

        $this->error('Gagal membuat berkas PDF. Pastikan Google Chrome atau Chromium terpasang.');
        return Command::FAILURE;
    }
}
