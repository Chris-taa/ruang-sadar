<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    // Ambil semua jurnal milik user, bisa filter by date (?date=2026-06-22)
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

    // Simpan jurnal baru
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

    // Detail satu jurnal
    public function show(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $journal,
        ]);
    }

    // Update jurnal
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

    // Hapus jurnal
    public function destroy(Request $request, $id)
    {
        $journal = Journal::where('user_id', $request->user()->id)->findOrFail($id);
        $journal->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurnal berhasil dihapus!',
        ]);
    }

    // Untuk dot marker kalender — tanggal mana aja yang udah ada jurnalnya
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