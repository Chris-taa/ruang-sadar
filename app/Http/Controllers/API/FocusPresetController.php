<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FocusPreset;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Focus Preset", description: "API untuk mengelola preset waktu fokus (Pomodoro/Timer)")]
class FocusPresetController extends Controller
{
    #[OA\Get(
        path: "/api/focus-preset",
        summary: "Ambil semua preset fokus milik pengguna",
        tags: ["Focus Preset"],
        description: "Menampilkan daftar preset waktu fokus (Pomodoro atau Timer) khusus untuk user yang sedang login.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data preset")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
    public function index(Request $request)
    {
        $presets = FocusPreset::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $presets,
        ]);
    }

    #[OA\Post(
        path: "/api/focus-preset",
        summary: "Simpan preset fokus baru",
        tags: ["Focus Preset"],
        description: "Menambahkan preset baru. Jika tipe 'pomodoro', maka `short_break` dan `rounds` wajib diisi.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "type", "focus_duration"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Belajar Android"),
                new OA\Property(property: "type", type: "string", enum: ["pomodoro", "timer"], example: "pomodoro"),
                new OA\Property(property: "focus_duration", type: "integer", example: 25, description: "Durasi fokus dalam menit"),
                new OA\Property(property: "short_break", type: "integer", example: 5, description: "Durasi istirahat pendek dalam menit"),
                new OA\Property(property: "long_break", type: "integer", example: 15, description: "Durasi istirahat panjang dalam menit"),
                new OA\Property(property: "rounds", type: "integer", example: 4, description: "Jumlah ronde sebelum istirahat panjang")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Preset berhasil disimpan")]
    #[OA\Response(response: 401, description: "Unauthenticated")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:pomodoro,timer',
            'focus_duration' => 'required|integer|min:1|max:480',
            'short_break'    => 'nullable|integer|min:1|max:60',
            'long_break'     => 'nullable|integer|min:1|max:120',
            'rounds'         => 'nullable|integer|min:1|max:20',
        ]);

        // Validasi tambahan khusus pomodoro
        if ($validated['type'] === 'pomodoro') {
            if (empty($validated['short_break']) || empty($validated['rounds'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'short_break dan rounds wajib diisi untuk tipe pomodoro.',
                ], 422);
            }
        }

        $preset = FocusPreset::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Preset berhasil disimpan!',
            'data'    => $preset,
        ], 201);
    }

    #[OA\Get(
        path: "/api/focus-preset/{id}",
        summary: "Detail satu preset fokus",
        tags: ["Focus Preset"],
        description: "Menampilkan detail satu preset berdasarkan ID.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Preset", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil mengambil detail preset")]
    #[OA\Response(response: 404, description: "Preset tidak ditemukan")]
    public function show(Request $request, $id)
    {
        $preset = FocusPreset::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $preset,
        ]);
    }

    #[OA\Put(
        path: "/api/focus-preset/{id}",
        summary: "Update preset fokus",
        tags: ["Focus Preset"],
        description: "Memperbarui data preset yang sudah ada.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Preset", schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Membaca Buku"),
                new OA\Property(property: "type", type: "string", enum: ["pomodoro", "timer"], example: "timer"),
                new OA\Property(property: "focus_duration", type: "integer", example: 45),
                new OA\Property(property: "short_break", type: "integer", example: null),
                new OA\Property(property: "long_break", type: "integer", example: null),
                new OA\Property(property: "rounds", type: "integer", example: null)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Preset berhasil diperbarui")]
    #[OA\Response(response: 404, description: "Preset tidak ditemukan")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function update(Request $request, $id)
    {
        $preset = FocusPreset::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:100',
            'type'           => 'sometimes|required|in:pomodoro,timer',
            'focus_duration' => 'sometimes|required|integer|min:1|max:480',
            'short_break'    => 'sometimes|nullable|integer|min:1|max:60',
            'long_break'     => 'sometimes|nullable|integer|min:1|max:120',
            'rounds'         => 'sometimes|nullable|integer|min:1|max:20',
        ]);

        $preset->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Preset berhasil diperbarui!',
            'data'    => $preset,
        ]);
    }

    #[OA\Delete(
        path: "/api/focus-preset/{id}",
        summary: "Hapus preset fokus",
        tags: ["Focus Preset"],
        description: "Menghapus preset fokus berdasarkan ID.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Preset", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Preset berhasil dihapus")]
    #[OA\Response(response: 404, description: "Preset tidak ditemukan")]
    public function destroy(Request $request, $id)
    {
        $preset = FocusPreset::where('user_id', $request->user()->id)->findOrFail($id);
        $preset->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Preset berhasil dihapus!',
        ]);
    }
}