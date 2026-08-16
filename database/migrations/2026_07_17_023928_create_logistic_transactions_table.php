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
        Schema::create('logistic_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistic_id')->constrained('logistics')->onDelete('cascade');
            $table->string('type'); // Masuk, Keluar
            $table->integer('quantity');
            $table->date('transaction_date');
            $table->string('recipient_or_donor')->nullable(); // Siapa penerima/penyumbang
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_transactions');
    }
};
