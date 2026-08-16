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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('meeting_date');
            $table->string('location')->nullable();
            $table->string('category')->default('Rapat Koordinasi');
            $table->string('leader')->nullable();
            $table->string('notewriter')->nullable();
            $table->json('attendees')->nullable();
            $table->text('agenda')->nullable();
            $table->text('summary')->nullable();
            $table->json('action_items')->nullable();
            $table->string('status')->default('Selesai');
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
