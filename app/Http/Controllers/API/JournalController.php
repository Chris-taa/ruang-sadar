<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Journal", description: "API untuk mengelola jurnal mindfulness")]
class JournalController extends Controller
{
    #[OA\Get(
        path: "/api/journal",
        summary: "Ambil riwayat jurnal pengguna",
        tags: ["Journal"],
        description: "Menampilkan daftar jurnal khusus milik user yang sedang login. Bisa difilter berdasarkan tanggal.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "date",
        in: "query",
        description: "Filter berdasarkan tanggal (Format: YYYY-MM-DD)",
        required: false,
        schema: new OA\Schema(type: "string", example: "2026-06-22")
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data jurnal")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
    public function index(Request $request)
    {
        $query = Journal::where('user_id', $request->user()->id)->latest('date');

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->get(),
        ]);
    }

    #[OA\Post(
        path: "/api/journal",
        summary: "Simpan jurnal baru",
        tags: ["Journal"],
        description: "Menambahkan catatan jurnal baru dengan detail mood dan penyebabnya",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["mood", "date"],
            properties: [
                new OA\Property(property: "mood", type: "string", example: "Senang"),
                new OA\Property(
                    property: "cause", 
                    type: "array", 
                    items: new OA\Items(type: "string"), 
                    example: ["Tugas Selesai", "Tidur Cukup"]
                ),
                new OA\Property(property: "contain", type: "string", example: "Hari ini sangat produktif dan menyenangkan."),
                new OA\Property(property: "date", type: "string", format: "date-time", example: "2026-06-22 18:30:00")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Jurnal berhasil disimpan")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mood'    => 'required|string|max:50',
            'cause'   => 'nullable|array',
            'cause.*' => 'string|max:100',
            'contain' => 'nullable|string',
            'date'    => 'required|date_format:Y-m-d H:i:s',
        ]);

        $journal = Journal::create([
            'user_id' => $request->user()->id,
            'mood'    => $validated['mood'],
            'cause'   => $validated['cause'] ?? null,
            'contain' => $validated['contain'] ?? null,
            'date'    => $validated['date'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurnal berhasil disimpan!',
            'data'    => $journal,
        ], 201);
    }

    #[OA\Get(
        path: "/api/journal/{id}",
        summary: "Detail satu jurnal",
        tags: ["Journal"],
        description: "Menampilkan detail satu jurnal berdasarkan ID",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Jurnal", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil mengambil detail jurnal")]
    #[OA\Response(response: 404, description: "Jurnal tidak ditemukan")]
    public function show(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $journal,
        ]);
    }

    #[OA\Put(
        path: "/api/journal/{id}",
        summary: "Update jurnal",
        tags: ["Journal"],
        description: "Memperbarui data jurnal yang sudah ada",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Jurnal", schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "mood", type: "string", example: "Biasa Saja"),
                new OA\Property(
                    property: "cause", 
                    type: "array", 
                    items: new OA\Items(type: "string"), 
                    example: ["Hujan", "Bosan"]
                ),
                new OA\Property(property: "contain", type: "string", example: "Hari ini biasa saja, cuma di rumah."),
                new OA\Property(property: "date", type: "string", format: "date-time", example: "2026-06-22 19:00:00")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Jurnal berhasil diperbarui")]
    #[OA\Response(response: 404, description: "Jurnal tidak ditemukan")]
    public function update(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'mood'    => 'sometimes|required|string|max:50',
            'cause'   => 'sometimes|nullable|array',
            'cause.*' => 'string|max:100',
            'contain' => 'sometimes|nullable|string',
            'date'    => 'sometimes|required|date_format:Y-m-d H:i:s',
        ]);

        $journal->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurnal berhasil diperbarui!',
            'data'    => $journal,
        ]);
    }

    #[OA\Delete(
        path: "/api/journal/{id}",
        summary: "Hapus jurnal",
        tags: ["Journal"],
        description: "Menghapus catatan jurnal",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Jurnal", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Jurnal berhasil dihapus")]
    #[OA\Response(response: 404, description: "Jurnal tidak ditemukan")]
    public function destroy(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)->findOrFail($id);
        $journal->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurnal berhasil dihapus!',
        ]);
    }

    #[OA\Get(
        path: "/api/journal/dates/entries",
        summary: "Ambil tanggal yang ada jurnalnya",
        tags: ["Journal"],
        description: "Digunakan untuk marker dot di kalender aplikasi (menandai tanggal mana saja yang sudah diisi jurnal)",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data tanggal")]
    public function datesWithEntries(Request $request)
    {
        $dates = Journal::where('user_id', $request->user()->id)
            ->selectRaw('DATE(date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $dates,
        ]);
    }
}