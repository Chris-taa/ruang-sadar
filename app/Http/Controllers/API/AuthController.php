<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "API Otentikasi User")]
class AuthController extends Controller
{
    #[OA\Post(path: "/api/register", summary: "Register akun baru", tags: ["Auth"])]
    #[OA\Response(response: 201, description: "Registrasi berhasil")]
    #[OA\Response(response: 422, description: "Validasi gagal")]
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:patient,therapist',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil!',
            'token'   => $token,
            'data'    => $user
        ], 201);
    }

    #[OA\Post(path: "/api/login", summary: "Login user", tags: ["Auth"])]
    #[OA\Response(response: 200, description: "Login berhasil")]
    #[OA\Response(response: 401, description: "Email atau password salah")]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak valid',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'token'   => $token,
            'data'    => $user
        ]);
    }

    #[OA\Post(path: "/api/auth/google", summary: "Login dengan Google", tags: ["Auth"])]
    #[OA\Response(response: 200, description: "Login Google berhasil")]
    #[OA\Response(response: 401, description: "Token tidak valid")]
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token wajib diisi'
            ], 422);
        }

        try {
            $client  = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->id_token);

            if ($payload) {
                $user = User::firstOrCreate(
                    ['email' => $payload['email']],
                    [
                        'name'     => $payload['name'],
                        'username' => explode('@', $payload['email'])[0] . rand(100, 999),
                        'password' => Hash::make(\Illuminate\Support\Str::random(16)),
                        'role'     => 'patient'
                    ]
                );

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login Google berhasil',
                    'token'   => $token,
                    'data'    => $user
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Token Google tidak valid'
        ], 401);
    }
}