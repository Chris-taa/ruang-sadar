<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Video", description: "API untuk mengelola video")]
class VideoController extends Controller
{
    #[OA\Get(
        path: "/api/videos",
        summary: "Ambil semua daftar video",
        tags: ["Video"],
        description: "Mendapatkan list video, bisa difilter berdasarkan kategori melalui query parameter",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "category",
        in: "query",
        description: "Filter video berdasarkan kategori (opsional)",
        required: false,
        schema: new OA\Schema(type: "string", example: "Anxiety")
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data video")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
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

    #[OA\Get(
        path: "/api/videos/{id}",
        summary: "Ambil detail video berdasarkan ID",
        tags: ["Video"],
        description: "Mendapatkan data spesifik dari satu video",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID Video",
        schema: new OA\Schema(type: "integer", example: 1)
    )]
    #[OA\Response(response: 200, description: "Berhasil menemukan video")]
    #[OA\Response(response: 404, description: "Video tidak ditemukan")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
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