<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->string('member_number')->nullable()->unique();
            $table->string('name');
            $table->string('tier'); // Dewan Pembina, Dewan Pengawas, Pengurus, Anggota
            $table->string('position'); // Ketua Dewan Pembina, Ketua Umum, Sekretaris, Bendahara, Kabid, Anggota, dll.
            $table->string('division')->nullable(); // Rescue, Logistik, Humas, Keuangan, Umum, dll.
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('Aktif'); // Aktif, Tidak Aktif, Demisioner
            $table->string('period')->default('2024 - 2029'); // Periode Masa Jabatan
            $table->integer('order_index')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('volunteer_id')->nullable()->constrained('volunteers')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
