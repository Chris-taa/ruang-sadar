<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\JournalController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\FocusController;

Route::middleware('auth:sanctum')->group(function () {
    
    // --- FITUR JURNAL ---
    Route::post('/journal', [JournalController::class, 'store']);       // Pasien & Terapis bisa nulis
    Route::get('/journal', [JournalController::class, 'index']);        // Lihat riwayat jurnal sendiri

    // --- FITUR ARTIKEL ---
    Route::get('/articles', [ArticleController::class, 'index']);       // Pasien & Terapis bisa lihat semua artikel
    Route::get('/articles/{id}', [ArticleController::class, 'show']);   // Pasien & Terapis bisa baca detail artikel
    Route::post('/articles', [ArticleController::class, 'store']);      // Nanti di dalam kita proteksi (Hanya Terapis)

    // --- FITUR FOCUS MODE ---
    Route::post('/focus-session', [FocusController::class, 'store']);   // Simpan waktu fokus

    // --- FITUR QUIZ ---
    Route::get('/quizzes', [QuizController::class, 'index']);           // Lihat daftar kuis
    Route::post('/quizzes/submit', [QuizController::class, 'submit']);  // Kirim hasil kuis
    // --- FITUR CHAT --//
    Route::get('/chat/{receiver_id}', [ChatController::class, 'getChatHistory']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    // --- FITUR VIDEO --//
    Route::get('/videos', [VideoController::class, 'index']);     // Untuk list video
    Route::get('/videos/{id}', [VideoController::class, 'show']);
    
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
