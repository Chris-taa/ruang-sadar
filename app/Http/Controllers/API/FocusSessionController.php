<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FocusSession;
use App\Models\FocusPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Focus Session", description: "API untuk menjalankan dan merekap sesi fokus (Pomodoro/Timer)")]
class FocusSessionController extends Controller
{
    #[OA\Post(
        path: "/api/focus-session/start",
        summary: "Mulai sesi fokus baru",
        tags: ["Focus Session"],
        description: "Memulai sesi fokus. User tidak bisa memulai sesi baru jika masih ada sesi yang berstatus 'ongoing'.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["type", "planned_duration"],
            properties: [
                new OA\Property(property: "preset_id", type: "integer", nullable: true, example: 1, description: "ID dari Focus Preset (opsional)"),
                new OA\Property(property: "type", type: "string", enum: ["pomodoro", "timer"], example: "pomodoro"),
                new OA\Property(property: "planned_duration", type: "integer", example: 25, description: "Durasi rencana dalam menit")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Sesi fokus dimulai")]
    #[OA\Response(response: 409, description: "Masih ada sesi yang berjalan")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function start(Request $request)
    {
        // Cegah user mulai sesi baru kalau masih ada yang ongoing
        $ongoing = FocusSession::where('user_id', $request->user()->id)
            ->where('status', 'ongoing')
            ->first();

        if ($ongoing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Masih ada sesi fokus yang berjalan.',
                'data'    => $ongoing,
            ], 409);
        }

        $validated = $request->validate([
            'preset_id'        => 'nullable|exists:focus_presets,id',
            'type'             => 'required|in:pomodoro,timer',
            'planned_duration' => 'required|integer|min:1|max:480',
        ]);

        // Kalau pakai preset, pastikan preset milik user sendiri
        if (!empty($validated['preset_id'])) {
            FocusPreset::where('user_id', $request->user()->id)
                ->findOrFail($validated['preset_id']);
        }

        $session = FocusSession::create([
            'user_id'          => $request->user()->id,
            'preset_id'        => $validated['preset_id'] ?? null,
            'type'             => $validated['type'],
            'planned_duration' => $validated['planned_duration'],
            'actual_duration'  => 0,
            'started_at'       => Carbon::now(),
            'status'           => 'ongoing',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesi fokus dimulai!',
            'data'    => $session,
        ], 201);
    }

    #[OA\Post(
        path: "/api/focus-session/{id}/end",
        summary: "Selesaikan atau batalkan sesi fokus",
        tags: ["Focus Session"],
        description: "Mengakhiri sesi fokus yang sedang berjalan dengan mengubah statusnya menjadi completed atau cancelled.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID Sesi Fokus", schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status", "actual_duration"],
            properties: [
                new OA\Property(property: "status", type: "string", enum: ["completed", "cancelled"], example: "completed"),
                new OA\Property(property: "actual_duration", type: "integer", example: 25, description: "Durasi aktual yang diselesaikan dalam menit")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Sesi fokus selesai atau dibatalkan")]
    #[OA\Response(response: 404, description: "Sesi tidak ditemukan atau tidak sedang berjalan")]
    public function end(Request $request, $id)
    {
        $session = FocusSession::where('user_id', $request->user()->id)
            ->where('status', 'ongoing')
            ->findOrFail($id);

        $validated = $request->validate([
            'status'          => 'required|in:completed,cancelled',
            'actual_duration' => 'required|integer|min:0',
        ]);

        $session->update([
            'status'          => $validated['status'],
            'actual_duration' => $validated['actual_duration'],
            'ended_at'        => Carbon::now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $validated['status'] === 'completed'
                ? 'Sesi fokus selesai! Kerja bagus 💪'
                : 'Sesi fokus dibatalkan.',
            'data'    => $session,
        ]);
    }

    #[OA\Get(
        path: "/api/focus-session/ongoing",
        summary: "Cek sesi yang sedang berjalan",
        tags: ["Focus Session"],
        description: "Mendapatkan data sesi fokus milik user yang saat ini masih berstatus 'ongoing'.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil data sesi (bisa mengembalikan null jika tidak ada)")]
    public function ongoing(Request $request)
    {
        $session = FocusSession::where('user_id', $request->user()->id)
            ->where('status', 'ongoing')
            ->with('preset')
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => $session,
        ]);
    }

    #[OA\Get(
        path: "/api/focus-session/history",
        summary: "Riwayat sesi fokus",
        tags: ["Focus Session"],
        description: "Menampilkan riwayat semua sesi fokus (completed/cancelled) dengan fitur pagination dan filter tanggal.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "per_page", in: "query", required: false, description: "Jumlah data per halaman", schema: new OA\Schema(type: "integer", example: 15))]
    #[OA\Parameter(name: "date", in: "query", required: false, description: "Filter tanggal (Format YYYY-MM-DD)", schema: new OA\Schema(type: "string", example: "2026-06-22"))]
    #[OA\Response(response: 200, description: "Berhasil mengambil riwayat sesi")]
    public function history(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'date'     => 'nullable|date_format:Y-m-d',
        ]);

        $query = FocusSession::where('user_id', $request->user()->id)
            ->where('status', '!=', 'ongoing')
            ->with('preset')
            ->latest('started_at');

        if (!empty($validated['date'])) {
            $query->whereDate('started_at', $validated['date']);
        }

        $perPage = $validated['per_page'] ?? 15;
        $history = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    #[OA\Get(
        path: "/api/focus-session/stats",
        summary: "Statistik total jam fokus",
        tags: ["Focus Session"],
        description: "Mengambil rangkuman statistik waktu fokus user (total menit, hari ini, minggu ini, bulan ini) serta statistik harian untuk grafik.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil statistik fokus")]
    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        $baseQuery = FocusSession::where('user_id', $userId)
            ->where('status', 'completed');

        // Total semua waktu
        $totalMinutes = (clone $baseQuery)->sum('actual_duration');

        // Total hari ini
        $todayMinutes = (clone $baseQuery)
            ->whereDate('started_at', Carbon::today())
            ->sum('actual_duration');

        // Total minggu ini
        $weekMinutes = (clone $baseQuery)
            ->whereBetween('started_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('actual_duration');

        // Total bulan ini
        $monthMinutes = (clone $baseQuery)
            ->whereMonth('started_at', Carbon::now()->month)
            ->whereYear('started_at', Carbon::now()->year)
            ->sum('actual_duration');

        // Jumlah sesi
        $totalSessions    = (clone $baseQuery)->count();
        $cancelledSessions = FocusSession::where('user_id', $userId)
            ->where('status', 'cancelled')
            ->count();

        // History harian 7 hari terakhir
        $dailyStats = FocusSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('started_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(started_at) as date, SUM(actual_duration) as total_minutes, COUNT(*) as sessions')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_hours'        => round($totalMinutes / 60, 1),
                'total_minutes'      => $totalMinutes,
                'today_hours'        => round($todayMinutes / 60, 1),
                'today_minutes'      => $todayMinutes,
                'week_hours'         => round($weekMinutes / 60, 1),
                'week_minutes'       => $weekMinutes,
                'month_hours'        => round($monthMinutes / 60, 1),
                'month_minutes'      => $monthMinutes,
                'total_sessions'     => $totalSessions,
                'cancelled_sessions' => $cancelledSessions,
                'daily_stats'        => $dailyStats,
            ],
        ]);
    }
}