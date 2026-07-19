<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    // Menampilkan profile user
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'vehicles' => $user->vehicles
            ]
        ]);
    }

    public function allUsers()
{
    $users = User::with('vehicles')->get();

    return response()->json([
        'success' => true,
        'data' => $users
    ]);
}


    // Update profile
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id
        ]);


        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);


        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'data' => $user
        ]);
    }
}