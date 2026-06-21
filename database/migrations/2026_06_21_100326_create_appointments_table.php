<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel users untuk Pasien
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            // Menghubungkan ke tabel users untuk Terapis/Psikolog
            $table->foreignId('therapist_id')->constrained('users')->onDelete('cascade');
            
            // Data dari form UI
            $table->string('full_name');
            $table->date('consultation_date');
            $table->text('topic');
            $table->enum('method', ['online', 'offline']);
            
            // Status jadwal (pending = menunggu konfirmasi psikolog)
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};