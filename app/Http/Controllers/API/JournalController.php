<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class JournalController extends Controller
{
    #[OA\Get(
        path: "/api/journal",
        summary: "Ambil riwayat jurnal pengguna",
        tags: ["Journal"],
        description: "Menampilkan daftar jurnal khusus milik user yang sedang login",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data jurnal")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
    public function index(Request $request)
    {
        $journals = Journal::where('user_id', $request->user()->id)->latest()->get();
        return response()->json(['status' => 'success', 'data' => $journals]);
    }

    #[OA\Post(
        path: "/api/journal",
        summary: "Simpan jurnal baru",
        tags: ["Journal"],
        description: "Menambahkan catatan jurnal baru untuk user yang sedang login",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["title", "content"],
            properties: [
                new OA\Property(property: "title", type: "string", example: "Fokus Hari Ini"),
                new OA\Property(property: "content", type: "string", example: "Hari ini saya merasa lebih tenang dan produktif setelah meditasi pagi.")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Jurnal berhasil disimpan")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Otomatis mengisi user_id dari siapa yang sedang login (bisa pasien / terapis)
        $journal = Journal::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'Jurnal berhasil disimpan!', 'data' => $journal]);
    }
}