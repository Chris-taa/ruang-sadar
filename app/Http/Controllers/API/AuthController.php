<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "API Otentikasi User")]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        summary: "Register akun baru",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "username", "password", "role"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Christian Candra"),
                new OA\Property(property: "email", type: "string", format: "email", example: "chris@example.com"),
                new OA\Property(property: "username", type: "string", example: "chris123"),
                new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                new OA\Property(property: "role", type: "string", example: "patient", description: "Pilihan: patient atau therapist")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Registrasi berhasil")]
    #[OA\Response(response: 422, description: "Data tidak valid")]
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:patient,therapist',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        summary: "Login user",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "chris@example.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "password123")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Login berhasil")]
    #[OA\Response(response: 401, description: "Email atau password salah")]
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    #[OA\Post(
        path: "/api/auth/google",
        summary: "Login dengan akun Google",
        tags: ["Auth"],
        description: "Menerima id_token dari Android, memvalidasinya, dan mengembalikan token akses"
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_token"],
            properties: [
                new OA\Property(property: "id_token", type: "string", description: "ID Token dari Google Sign-In Android")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Berhasil login/register via Google")]
    #[OA\Response(response: 401, description: "Token Google tidak valid")]
    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        // Inisialisasi Google Client
        $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        
        // Verifikasi token dari Android
        $payload = $client->verifyIdToken($request->id_token);

        if ($payload) {
            $googleEmail = $payload['email'];
            $googleName = $payload['name'];

            // Cek apakah user sudah ada. Jika belum, otomatis buat akun baru sebagai pasien
            $user = User::firstOrCreate(
                ['email' => $googleEmail],
                [
                    'name' => $googleName,
                    'username' => explode('@', $googleEmail)[0] . rand(100, 999),
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)), 
                    'role' => 'patient' 
                ]
            );

            // Buat token akses Laravel
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login Google berhasil',
                'data' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'Token Google tidak valid'
            ], 401);
        }
    }
}