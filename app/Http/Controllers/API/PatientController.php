<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use OpenApi\Attributes as OA;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

#[OA\Tag(name: "Patient", description: "API untuk fitur dan data khusus pasien")]
class PatientController extends Controller
{
    #[OA\Post(
        path: "/api/patients/appointments",
        summary: "Buat jadwal konsultasi baru",
        tags: ["Patient"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 201, description: "Jadwal konsultasi berhasil dibuat")] // ✨ Tambahan wajib
    public function bookSchedule(Request $request)
    {
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pasien yang dapat mengatur jadwal konsultasi.'
            ], 403);
        }

        $validated = $request->validate([
            'therapist_id'      => 'required|exists:users,id',
            'full_name'         => 'required|string|max:255',
            'consultation_date' => 'required|date',
            'topic'             => 'required|string',
            'method'            => 'required|in:online,offline',
        ]);

        $appointment = Appointment::create([
            'patient_id'        => $request->user()->id,
            'therapist_id'      => $validated['therapist_id'],
            'full_name'         => $validated['full_name'],
            'consultation_date' => $validated['consultation_date'],
            'topic'             => $validated['topic'],
            'method'            => $validated['method'],
            'status'            => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal konsultasi berhasil dibuat!',
            'data'    => $appointment
        ], 201);
    }

    #[OA\Get(
        path: "/api/patients/appointments",
        summary: "Ambil semua jadwal konsultasi pasien",
        tags: ["Patient"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil jadwal")] // ✨ Tambahan wajib
    public function getAppointments(Request $request)
    {
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $appointments = Appointment::where('patient_id', $request->user()->id)
            ->with('therapist:id,name,role')
            ->orderBy('consultation_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil jadwal.',
            'data'    => $appointments
        ]);
    }

    #[OA\Post(
        path: "/api/patients/profile",
        summary: "Ubah profil pasien (Termasuk Foto)",
        tags: ["Patient"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Profil berhasil diperbarui")] // ✨ Tambahan wajib
    public function updateProfile(Request $request)
    {
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini khusus untuk pasien.'
            ], 403);
        }

        $user = $request->user();

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'photo'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }

        if ($request->hasFile('photo')) {
            $uploadedFileUrl             = Cloudinary::upload($request->file('photo')->getRealPath())->getSecurePath();
            $updateData['profile_picture'] = $uploadedFileUrl;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'data'    => $user
        ]);
    }

    #[OA\Get(
        path: "/api/patients/therapists",
        summary: "Ambil daftar psikolog",
        tags: ["Patient"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil daftar psikolog")] // ✨ Tambahan wajib
    public function getTherapists(Request $request)
    {
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $therapists = User::where('role', 'therapist')->get();

        return response()->json([
            'success' => true,
            'data'    => $therapists
        ]);
    }
}