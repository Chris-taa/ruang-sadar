<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focus_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name');                                         // Nama preset, misal "Deep Work"
            $table->enum('type', ['pomodoro', 'timer']);                    // Tipe timer

            // Durasi dalam menit
            $table->unsignedInteger('focus_duration');                      // Durasi fokus utama
            $table->unsignedInteger('short_break')->nullable();             // Pomodoro: istirahat pendek
            $table->unsignedInteger('long_break')->nullable();              // Pomodoro: istirahat panjang
            $table->unsignedInteger('rounds')->nullable();                  // Pomodoro: sesi sebelum long break

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focus_presets');
    }
};