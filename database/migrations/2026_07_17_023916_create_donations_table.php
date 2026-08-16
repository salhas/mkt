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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->nullable()->constrained('donors')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->date('donation_date');
            $table->string('payment_method'); // Bank Transfer, E-Wallet, Cash, dll.
            $table->string('status')->default('Pending'); // Pending, Sukses, Gagal
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
