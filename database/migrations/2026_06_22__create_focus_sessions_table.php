<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('focus_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('preset_id')->nullable()->constrained('focus_presets')->onDelete('set null');

            $table->enum('type', ['pomodoro', 'timer']);
            $table->unsignedInteger('planned_duration');                    // Durasi rencana (menit)
            $table->unsignedInteger('actual_duration')->default(0);        // Durasi aktual (menit)
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focus_sessions');
    }
};