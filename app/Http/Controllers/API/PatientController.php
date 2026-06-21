<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Patient", description: "API untuk fitur dan data khusus pasien")]
class PatientController extends Controller
{
    #[OA\Post(
        path: "/api/patients/appointments",
        summary: "Buat jadwal konsultasi baru",
        tags: ["Patient"],
        description: "Menyimpan data dari form Atur Jadwal",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["therapist_id", "full_name", "consultation_date", "topic", "method"],
            properties: [
                new OA\Property(property: "therapist_id", type: "integer", example: 2, description: "ID Psikolog/Terapis yang dipilih dari list"),
                new OA\Property(property: "full_name", type: "string", example: "Budi Santoso"),
                new OA\Property(property: "consultation_date", type: "string", format: "date", example: "2026-06-25"),
                new OA\Property(property: "topic", type: "string", example: "Saya sering merasa cemas akhir-akhir ini karena tugas menumpuk."),
                new OA\Property(property: "method", type: "string", example: "online")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Jadwal berhasil dibuat")]
    #[OA\Response(response: 403, description: "Akses ditolak (Bukan Pasien)")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function bookSchedule(Request $request)
    {
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pasien yang dapat mengatur jadwal konsultasi.'
            ], 403);
        }

        $validated = $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'full_name' => 'required|string|max:255',
            'consultation_date' => 'required|date',
            'topic' => 'required|string',
            'method' => 'required|in:online,offline',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $request->user()->id,
            'therapist_id' => $validated['therapist_id'],
            'full_name' => $validated['full_name'],
            'consultation_date' => $validated['consultation_date'],
            'topic' => $validated['topic'],
            'method' => $validated['method'],
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Jadwal konsultasi berhasil dibuat!', 
            'data' => $appointment
        ], 201);
    }

    #[OA\Put(
        path: "/api/patients/profile",
        summary: "Ubah profil pasien",
        tags: ["Patient"],
        description: "Mengupdate data nama dan username khusus untuk pasien yang sedang login",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "username"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Christian Hadi Candra"),
                new OA\Property(property: "username", type: "string", example: "christian_candra")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Profil berhasil diperbarui")]
    #[OA\Response(response: 403, description: "Akses ditolak (Bukan Pasien)")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function updateProfile(Request $request)
    {
        // 1. Pastikan hanya user dengan role patient yang bisa akses rute ini
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus untuk pasien.'
            ], 403);
        }

        $user = $request->user();

        // 2. Validasi input data baru
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
        ]);

        // 3. Proses update ke database
        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui!',
            'data' => $user
        ]);
    }
}