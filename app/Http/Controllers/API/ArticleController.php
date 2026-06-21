<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Articles", description="API untuk pengelolaan artikel")
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/articles",
     * tags={"Articles"},
     * summary="Dapatkan daftar semua artikel",
     * @OA\Response(response=200, description="Berhasil mendapatkan data artikel")
     * )
     */
    public function index()
    {
        $articles = Article::latest()->get();
        return response()->json(['status' => 'success', 'data' => $articles]);
    }

    /**
     * @OA\Post(
     * path="/api/articles",
     * tags={"Articles"},
     * summary="Tulis artikel baru (Khusus Terapis)",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"title","content"},
     * @OA\Property(property="title", type="string", example="Tips Menjaga Kesehatan Mental"),
     * @OA\Property(property="content", type="string", example="Isi artikel kesehatan mental di sini..."),
     * @OA\Property(property="youtube_url", type="string", example="https://youtube.com/...")
     * )
     * ),
     * @OA\Response(response=200, description="Artikel berhasil dibuat"),
     * @OA\Response(response=403, description="Akses ditolak (Bukan Terapis)"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'therapist') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Hanya Terapis yang dapat menulis artikel.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'youtube_url' => 'nullable|url',
        ]);

        $article = Article::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Artikel berhasil dibuat!', 'data' => $article]);
    }
}