<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => 'mahasiswa',
    ]);

    return response()->json([
        'message' => 'Register berhasil',
        'user' => $user,
    ], 201);
}


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);


        $user = User::where('email', $request->email)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {

            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);

        }


        $token = $user->createToken('smart-parking-token')
            ->plainTextToken;


        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();


        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}