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

    #[OA\Post( // Ubah dari PUT menjadi POST karena mengurus file upload (multipart/form-data)
        path: "/api/patients/profile",
        summary: "Ubah profil pasien (Termasuk Foto)",
        tags: ["Patient"],
        description: "Mengupdate data nama, username, dan foto profil. Gunakan method POST dengan Content-Type: multipart/form-data.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Christian Hadi Candra"),
                    new OA\Property(property: "username", type: "string", example: "christian_candra"),
                    new OA\Property(property: "photo", type: "string", format: "binary", description: "File foto (Opsional)")
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Profil berhasil diperbarui")]
    #[OA\Response(response: 403, description: "Akses ditolak (Bukan Pasien)")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function updateProfile(Request $request)
    {
        // 1. Pastikan hanya user dengan role patient yang bisa akses
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus untuk pasien.'
            ], 403);
        }

        $user = $request->user();

        // 2. Validasi input
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi foto (maks 2MB)
        ]);

        // Siapkan array data yang mau diupdate
        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        
        if (isset($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }

        // 3. Proses upload foto ke Cloudinary jika file foto disertakan
        // 3. Proses upload foto ke Cloudinary
        if ($request->hasFile('photo')) {
            $uploadedFileUrl = Cloudinary::upload($request->file('photo')->getRealPath())->getSecurePath();
            $updateData['profile_picture'] = $uploadedFileUrl;
        }

        // 4. Update database
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui!',
            'data' => $user
        ]);
    }

    #[OA\Get(
        path: "/api/patients/therapists",
        summary: "Ambil daftar psikolog",
        tags: ["Patient"],
        description: "Mengambil semua user dengan role therapist",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil daftar psikolog")]
    public function getTherapists(Request $request)
    {
        if ($request->user()->role !== 'patient') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Ambil semua user yang memiliki role 'therapist'
        $therapists = User::where('role', 'therapist')->get();

        return response()->json([
            'status' => 'success',
            'data' => $therapists
        ]);
    }
}