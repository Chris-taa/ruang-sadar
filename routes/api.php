<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\JournalController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\TherapistController;
use App\Http\Controllers\API\FocusPresetController;
use App\Http\Controllers\API\FocusSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Membungkus rute dalam middleware 'api' memastikan Laravel 
| memperlakukan request sebagai API dan memberikan respons JSON.
*/

Route::middleware(['api'])->group(function () {
    // --- RUTE PUBLIK ---
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'googleLogin']);

    // ✨ PINDAHKAN KE SINI (Di luar auth:sanctum)
    Route::get('/clear-cache', function () {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return response()->json(['message' => 'Cache server Railway berhasil dibersihkan!']);
    });

    // --- RUTE TERPROTEKSI ---
    Route::middleware('auth:sanctum')->group(function () {
        
        // FITUR PASIEN
        Route::get('/patients/therapists', [PatientController::class, 'getTherapists']);
        Route::post('/patients/appointments', [PatientController::class, 'bookSchedule']);
        Route::post('/patients/profile', [PatientController::class, 'updateProfile']);
        Route::get('/patients/appointments', [PatientController::class, 'getAppointments']);

        // FITUR TERAPIS
        Route::post('/therapists/profile', [TherapistController::class, 'updateProfile']);
        Route::get('/therapists/appointments', [TherapistController::class, 'getAppointments']);
        Route::patch('/therapists/appointments/{id}/status', [TherapistController::class, 'updateAppointmentStatus']);

        // FITUR JURNAL
        Route::get('/journal/dates/entries', [JournalController::class, 'datesWithEntries']);
        Route::apiResource('journal', JournalController::class);

        // FITUR ARTIKEL
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/articles/{id}', [ArticleController::class, 'show']);
        Route::post('/articles', [ArticleController::class, 'store']);

        // FITUR FOCUS MODE
        Route::apiResource('focus-preset', FocusPresetController::class);
        Route::prefix('focus-session')->group(function () {
            Route::post('/start', [FocusSessionController::class, 'start']);
            Route::post('/{id}/end', [FocusSessionController::class, 'end']);
            Route::get('/ongoing', [FocusSessionController::class, 'ongoing']);
            Route::get('/history', [FocusSessionController::class, 'history']);
            Route::get('/stats', [FocusSessionController::class, 'stats']);
        });

        // FITUR QUIZ
        Route::get('/quizzes', [QuizController::class, 'index']);
        Route::post('/quizzes/submit', [QuizController::class, 'submit']);

        // FITUR CHAT
        Route::get('/chat/{receiver_id}', [ChatController::class, 'getChatHistory']);
        Route::post('/chat/send', [ChatController::class, 'sendMessage']);

        // FITUR VIDEO
        Route::get('/videos', [VideoController::class, 'index']);
        Route::get('/videos/{id}', [VideoController::class, 'show']);

        // USER INFO
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        
    });
});