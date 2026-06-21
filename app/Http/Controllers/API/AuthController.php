<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(name="Auth", description="API Otentikasi User")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/register",
     * tags={"Auth"},
     * summary="Register akun baru",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name","email","username","password","role"},
     * @OA\Property(property="name", type="string", example="Christian Candra"),
     * @OA\Property(property="email", type="string", format="email", example="chris@example.com"),
     * @OA\Property(property="username", type="string", example="chris123"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="role", type="string", enum={"patient", "therapist"})
     * )
     * ),
     * @OA\Response(response=201, description="Registrasi berhasil"),
     * @OA\Response(response=422, description="Data tidak valid")
     * )
     */
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

    /**
     * @OA\Post(
     * path="/api/login",
     * tags={"Auth"},
     * summary="Login user",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", format="email", example="chris@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(response=200, description="Login berhasil"),
     * @OA\Response(response=401, description="Email atau password salah")
     * )
     */
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
}