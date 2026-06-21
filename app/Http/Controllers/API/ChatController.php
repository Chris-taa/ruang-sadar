<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Chat", description="API untuk fitur chat")
 */
class ChatController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/chat/{receiver_id}",
     * tags={"Chat"},
     * summary="Ambil riwayat chat",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="receiver_id",
     * in="path",
     * required=true,
     * description="ID lawan bicara",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Berhasil mengambil riwayat chat"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getChatHistory(Request $request, $receiver_id)
    {
        $sender_id = $request->user()->id;

        $chats = Message::where(function($query) use ($sender_id, $receiver_id) {
                    $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
                })->orWhere(function($query) use ($sender_id, $receiver_id) {
                    $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
                })
                ->orderBy('created_at', 'asc')
                ->get();

        return response()->json(['status' => 'success', 'data' => $chats]);
    }

    /**
     * @OA\Post(
     * path="/api/chat/send",
     * tags={"Chat"},
     * summary="Kirim pesan baru",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"receiver_id","message"},
     * @OA\Property(property="receiver_id", type="integer", example=2),
     * @OA\Property(property="message", type="string", example="Halo, bagaimana kabarmu?")
     * )
     * ),
     * @OA\Response(response=200, description="Pesan terkirim"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'Pesan terkirim!', 'data' => $message]);
    }
}