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
    Schema::create('videos', function (Blueprint $table) {
        $table->id();                               // ID_Video (Auto Increment)
        $table->string('title');                    // judul
        $table->string('video_url');                 // link youtube
        $table->string('category');                 // kategori (misal: 'Anxiety', 'Meditation')
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('videos');
}
};
