<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'birthdate' => 'nullable|date',
        ]);

        $trainer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'trainer',
            'phone_number' => $data['phone_number'] ?? null,
            'gender' => $data['gender'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
        ]);

        // Login automatico (opzionale ma comodo in SPA)
        Auth::login($trainer);

        return response()->json([
            'message' => 'Trainer registrato con successo',
            'user' => $trainer,
        ], 201);
    }
}
