<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('journals', function (Blueprint $table) {
            $table->id(); // Ini otomatis menjadi (PK) ID_Journal
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // (FK) ID_User
            
            $table->string('mood'); // Menyimpan emoji atau teks (misal: "sad")
            
            // Menggunakan tipe JSON karena cause bisa lebih dari satu (array)
            $table->json('cause')->nullable(); 
            
            $table->text('contain')->nullable(); // Isi curhatan/jurnal
            
            // Menggunakan dateTime karena di gambar ada tanggal kalender & jam (Record time 15.30)
            $table->dateTime('date'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
