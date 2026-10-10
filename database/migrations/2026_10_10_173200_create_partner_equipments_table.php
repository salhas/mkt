<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('partner_equipments');

        Schema::create('partner_equipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->index();
            $table->string('item_code')->nullable(); // Misal: EQ-001, ALUT-SAR-01
            $table->string('name'); // Misal: Perahu Karet LCR 4.2M, Genset 5KVA, dll.
            $table->string('category'); // Water Rescue, Vertical Rescue, Medis & Evakuasi, Komunikasi & Navigasi, dll.
            $table->integer('quantity')->default(1);
            $table->string('unit')->default('Unit'); // Unit, Set, Pcs, Box, Roll, Paket
            $table->string('condition')->default('Siap Pakai'); // Siap Pakai, Baik, Rusak Ringan, Rusak Berat, Dalam Perawatan
            $table->string('storage_location')->nullable(); // Misal: Gudang Utama, Mobil Rescue, Posko SAR
            $table->string('ownership_status')->default('Milik Sendiri'); // Milik Sendiri, Pinjam Pakai, Hibah / Bantuan, Sewa
            $table->string('status')->default('Tersedia'); // Tersedia, Sedang Digunakan, Maintenance, Tidak Aktif
            $table->text('notes')->nullable(); // Spesifikasi / Catatan teknis
            $table->string('photo_path')->nullable(); // Foto alat/perlengkapan
            $table->timestamps();
        });

        // Coba pasang foreign key jika didukung oleh engine database MySQL di hosting
        try {
            Schema::table('partner_equipments', function (Blueprint $table) {
                $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // Abaikan jika MySQL hosting menggunakan MyISAM atau collation mismatch
        }

        Schema::enableForeignKeyConstraints();

        // Seed data contoh peralatan untuk mitra yang ada jika belum ada data peralatan
        if (DB::table('partner_equipments')->doesntExist()) {
            $partners = DB::table('partners')->get();
            foreach ($partners as $partner) {
                DB::table('partner_equipments')->insert([
                    [
                        'partner_id' => $partner->id,
                        'item_code' => 'ALUT-' . str_pad($partner->id, 2, '0', STR_PAD_LEFT) . '-01',
                        'name' => 'Perahu Karet LCR 420 Heavy Duty + Mesin Tempel 25 PK',
                        'category' => 'Water Rescue',
                        'quantity' => 2,
                        'unit' => 'Set',
                        'condition' => 'Siap Pakai',
                        'storage_location' => 'Gudang Posko SAR Unit',
                        'ownership_status' => 'Milik Sendiri',
                        'status' => 'Tersedia',
                        'notes' => 'Lengkap dengan dayung, tangki bensin 24L, dan pompa manual. Terakhir diservis bulan lalu.',
                        'photo_path' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'partner_id' => $partner->id,
                        'item_code' => 'ALUT-' . str_pad($partner->id, 2, '0', STR_PAD_LEFT) . '-02',
                        'name' => 'Tandu Basket (Basket Stretcher) & Spinal Board Evakuasi',
                        'category' => 'Medis & Evakuasi',
                        'quantity' => 4,
                        'unit' => 'Unit',
                        'condition' => 'Baik',
                        'storage_location' => 'Ambulans & Posko Medis',
                        'ownership_status' => 'Milik Sendiri',
                        'status' => 'Tersedia',
                        'notes' => 'Termasuk safety strap pengikat pasien dan spider strap lengkap.',
                        'photo_path' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'partner_id' => $partner->id,
                        'item_code' => 'ALUT-' . str_pad($partner->id, 2, '0', STR_PAD_LEFT) . '-03',
                        'name' => 'Radio Komunikasi HT VHF Dual Band Waterproof',
                        'category' => 'Komunikasi & Navigasi',
                        'quantity' => 8,
                        'unit' => 'Unit',
                        'condition' => 'Siap Pakai',
                        'storage_location' => 'Ruang Komunikasi Pusdalops',
                        'ownership_status' => 'Milik Sendiri',
                        'status' => 'Tersedia',
                        'notes' => 'Baterai cadangan dan charger dock lengkap untuk operasi lapangan.',
                        'photo_path' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'partner_id' => $partner->id,
                        'item_code' => 'ALUT-' . str_pad($partner->id, 2, '0', STR_PAD_LEFT) . '-04',
                        'name' => 'Tenda Pleton Posko Bencana 6x12 Meter',
                        'category' => 'Shelter & Tenda',
                        'quantity' => 1,
                        'unit' => 'Set',
                        'condition' => 'Baik',
                        'storage_location' => 'Gudang Logistik Kebencanaan',
                        'ownership_status' => 'Hibah / Bantuan',
                        'status' => 'Tersedia',
                        'notes' => 'Kapasitas 40-50 orang pengungsi, rangka besi pipa kuat dan pasak lengkap.',
                        'photo_path' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('partner_equipments');
        Schema::enableForeignKeyConstraints();
    }
};
