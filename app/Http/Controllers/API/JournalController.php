<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    // Ambil jurnal milik user yang sedang login saja
    public function index(Request $request)
    {
        $journals = Journal::where('user_id', $request->user()->id)->latest()->get();
        return response()->json(['status' => 'success', 'data' => $journals]);
    }

    // Simpan jurnal baru
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