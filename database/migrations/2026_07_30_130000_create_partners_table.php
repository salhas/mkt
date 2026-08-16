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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->string('category'); // PMI, Rumah Sakit, Tim Rescue, Basarnas, BPBD, Filantropi
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('status')->default('Aktif'); // Aktif, Siaga Bencana, Evaluasi
            $table->string('mou_number')->nullable();
            $table->integer('personnel_count')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('volunteers', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('id')->constrained('partners')->nullOnDelete();
            $table->string('photo_path')->nullable()->after('address');
            $table->string('certifications')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn(['partner_id', 'photo_path', 'certifications']);
        });

        Schema::dropIfExists('partners');
    }
};
