<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FocusSession;
use App\Models\FocusPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FocusSessionController extends Controller
{
    public function start(Request $request)
    {
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
            // ✅ FIX: tambah study, relax, work, custom
            'type'             => 'required|in:pomodoro,timer,study,relax,work,custom',
            'planned_duration' => 'required|integer|min:1|max:480',
        ]);

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

    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        $baseQuery = FocusSession::where('user_id', $userId)
            ->where('status', 'completed');

        $totalMinutes = (clone $baseQuery)->sum('actual_duration');
        $todayMinutes = (clone $baseQuery)
            ->whereDate('started_at', Carbon::today())
            ->sum('actual_duration');
        $weekMinutes = (clone $baseQuery)
            ->whereBetween('started_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('actual_duration');
        $monthMinutes = (clone $baseQuery)
            ->whereMonth('started_at', Carbon::now()->month)
            ->whereYear('started_at', Carbon::now()->year)
            ->sum('actual_duration');

        $totalSessions     = (clone $baseQuery)->count();
        $cancelledSessions = FocusSession::where('user_id', $userId)
            ->where('status', 'cancelled')
            ->count();

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