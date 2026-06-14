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
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        // ID Pengirim (bisa pasien / terapis)
        $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
        
        // ID Penerima (bisa pasien / terapis)
        $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
        
        // Isi Pesan Text
        $table->text('message');
        
        // Status pesan sudah dibaca atau belum
        $table->boolean('is_read')->default(false);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('messages');
}
};
