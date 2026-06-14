<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    // 1. Ambil semua list video (bisa difilter berdasarkan kategori kalau mau)
    public function index(Request $request)
    {
        $query = Video::query();

        // Jika dari aplikasi mobile mengirimkan filter kategori (misal: /videos?category=Anxiety)
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $videos = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $videos
        ]);
    }

    // 2. Ambil detail 1 video berdasarkan ID
    public function show($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'status' => 'error',
                'message' => 'Video tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $video
        ]);
    }
}
