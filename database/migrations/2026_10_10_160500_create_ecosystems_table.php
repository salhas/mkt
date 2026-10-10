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
        Schema::create('ecosystems', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: Ekosistem Lembaga Kemanusiaan Unhas (U-Humanity)
            $table->string('code')->nullable()->unique(); // Contoh: U-HUMANITY
            $table->string('region')->nullable(); // Contoh: Makassar, Sulawesi Selatan
            $table->string('lead_institution')->nullable(); // Contoh: Universitas Hasanuddin
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->string('pic_email')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('Aktif'); // Aktif, Non-Aktif
            $table->timestamps();
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->foreignId('ecosystem_id')->nullable()->after('id')->constrained('ecosystems')->nullOnDelete();
        });

        // Seed data contoh awal: Ekosistem Lembaga Kemanusiaan Unhas (U-Humanity)
        $ecosystemId = DB::table('ecosystems')->insertGetId([
            'name' => 'Ekosistem Lembaga Kemanusiaan Unhas (U-Humanity)',
            'code' => 'U-HUMANITY',
            'region' => 'Makassar, Sulawesi Selatan',
            'lead_institution' => 'Universitas Hasanuddin',
            'pic_name' => 'Koordinator U-Humanity',
            'pic_phone' => '081234567890',
            'pic_email' => 'u-humanity@unhas.ac.id',
            'description' => 'Sinergi dan kolaborasi unit kemanusiaan, potensi SAR, relawan kebencanaan, tim medis, dan jejaring filantropi di lingkungan civitas akademika Universitas Hasanuddin.',
            'status' => 'Aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Hubungkan mitra yang relevan (seperti SAR Unhas) ke ekosistem ini jika ada
        DB::table('partners')
            ->where('name', 'like', '%Unhas%')
            ->orWhere('description', 'like', '%Unhas%')
            ->update(['ecosystem_id' => $ecosystemId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['ecosystem_id']);
            $table->dropColumn('ecosystem_id');
        });

        Schema::dropIfExists('ecosystems');
    }
};
