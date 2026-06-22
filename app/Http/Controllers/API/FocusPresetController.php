<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FocusPreset;
use Illuminate\Http\Request;

class FocusPresetController extends Controller
{
    // Ambil semua preset milik user
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

    // Simpan preset baru
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

    // Detail satu preset
    public function show(Request $request, $id)
    {
        $preset = FocusPreset::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $preset,
        ]);
    }

    // Update preset
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

    // Hapus preset
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