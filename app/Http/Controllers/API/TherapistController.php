<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Therapist", description: "API untuk fitur dan data khusus terapis")]
class TherapistController extends Controller
{
    #[OA\Post( // Ubah dari PUT menjadi POST untuk mendukung upload file
        path: "/api/therapists/profile",
        summary: "Ubah profil terapis (Termasuk Foto)",
        tags: ["Therapist"],
        description: "Mengupdate data profil dan foto khusus untuk terapis yang sedang login. Gunakan method POST dengan Content-Type: multipart/form-data.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Dr. Dian Sastro, M.Psi"),
                    new OA\Property(property: "username", type: "string", example: "dian_sastro_psi"),
                    new OA\Property(property: "photo", type: "string", format: "binary", description: "File foto (Opsional)")
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Profil berhasil diperbarui")]
    #[OA\Response(response: 403, description: "Akses ditolak (Bukan Terapis)")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function updateProfile(Request $request)
    {
        if ($request->user()->role !== 'therapist') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus untuk terapis.'
            ], 403);
        }

        $user = $request->user();

        // Validasi input
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

        // Proses upload foto ke Cloudinary jika file foto disertakan
        if ($request->hasFile('photo')) {
            $uploadedFileUrl = cloudinary()->upload($request->file('photo')->getRealPath())->getSecurePath();
            $updateData['profile_picture'] = $uploadedFileUrl;
        }

        // Update database
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil terapis berhasil diperbarui!',
            'data' => $user
        ]);
    }

    #[OA\Get(
        path: "/api/therapists/appointments",
        summary: "Lihat daftar jadwal konsultasi",
        tags: ["Therapist"],
        description: "Mengambil semua jadwal pasien yang masuk ke terapis ini",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil jadwal")]
    #[OA\Response(response: 403, description: "Akses ditolak")]
    public function getAppointments(Request $request)
    {
        if ($request->user()->role !== 'therapist') {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        // Ambil jadwal di mana therapist_id sama dengan ID terapis yang sedang login
        $appointments = Appointment::where('therapist_id', $request->user()->id)
                            ->with('patient:id,name,email,profile_picture') // <-- Kutambahkan profile_picture agar foto pasien juga muncul di daftar jadwal
                            ->latest()
                            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $appointments
        ]);
    }

    #[OA\Patch(
        path: "/api/therapists/appointments/{id}/status",
        summary: "Ubah status jadwal konsultasi",
        tags: ["Therapist"],
        description: "Mengubah status jadwal menjadi approved, completed, atau cancelled",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID Jadwal (Appointment)",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string", example: "approved", description: "Pilihan: approved, completed, cancelled")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Status berhasil diubah")]
    #[OA\Response(response: 404, description: "Jadwal tidak ditemukan")]
    public function updateAppointmentStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'therapist') {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,completed,cancelled'
        ]);

        $appointment = Appointment::where('id', $id)
                                  ->where('therapist_id', $request->user()->id) // Pastikan ini jadwal milik dia
                                  ->first();

        if (!$appointment) {
            return response()->json(['status' => 'error', 'message' => 'Jadwal tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $appointment->update(['status' => $validated['status']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status jadwal berhasil diubah menjadi ' . $validated['status'],
            'data' => $appointment
        ]);
    }
}