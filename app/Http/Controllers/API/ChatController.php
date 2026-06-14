<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // 1. Ambil riwayat chat antara User yang login dengan lawan bicaranya
    public function getChatHistory(Request $request, $receiver_id)
    {
        $sender_id = $request->user()->id;

        // Mengambil semua pesan antara pengirim dan penerima (bolak-balik)
        $chats = Message::where(function($query) use ($sender_id, $receiver_id) {
                    $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
                })->orWhere(function($query) use ($sender_id, $receiver_id) {
                    $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
                })
                ->orderBy('created_at', 'asc')
                ->get();

        return response()->json(['status' => 'success', 'data' => $chats]);
    }

    // 2. Kirim Pesan Baru
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id, // Otomatis dari token login
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'Pesan terkirim!', 'data' => $message]);
    }
}