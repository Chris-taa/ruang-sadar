<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // 1. Pasien & Terapis BISA LIHAT semua artikel
    public function index()
    {
        $articles = Article::latest()->get();
        return response()->json(['status' => 'success', 'data' => $articles]);
    }

    // 2. HANYA TERAPIS yang bisa nulis artikel
    public function store(Request $request)
    {
        // Pengecekan Role di sini, Audi!
        if ($request->user()->role !== 'therapist') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Hanya Terapis yang dapat menulis artikel.'
            ], 403);
        }

        // Kalau lolos pengecekan, artikel baru disimpan ke database
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'youtube_url' => 'nullable|url', // Sekalian jika ada video YT pendukung
        ]);

        $article = Article::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Artikel berhasil dibuat!', 'data' => $article]);
    }
}
