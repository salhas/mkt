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
        // Add attendance_token & is_attendance_open to meetings table
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('attendance_token', 64)->nullable()->unique()->after('status');
            $table->boolean('is_attendance_open')->default(true)->after('attendance_token');
        });

        // Create meeting_attendances table for guest/attendee check-ins
        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('institution')->nullable(); // Instansi / Lembaga / Unsur
            $table->string('position')->nullable(); // Jabatan / Peran
            $table->mediumText('signature')->nullable(); // Base64 digital signature
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attended_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_attendances');

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['attendance_token', 'is_attendance_open']);
        });
    }
};
