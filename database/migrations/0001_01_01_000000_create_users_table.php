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
    Schema::create('users', function (Blueprint $table) {
        // Field Utama & Autentikasi
        $table->id();                                       // ID / ID_Therapist
        $table->string('name');                             // nama
        $table->string('username')->unique();               // username
        $table->string('email')->unique();                  // email
        $table->string('password');                         // pass
        $table->integer('age')->nullable();                 // umur
        $table->string('role');                             // 'patient' atau 'therapist'
        
        // Field Khusus Patient
        $table->text('about')->nullable();                  // Deskripsi diri patient
        
        // Field Khusus Therapist
        $table->string('license_id')->nullable();           // license ID untuk therapist
        
        // Field Tambahan Bersama
        $table->string('profile_picture')->nullable();      // Tempat simpan nama file foto profile
        
        // Bawaan Laravel
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
