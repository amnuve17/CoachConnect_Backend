<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    // Lista tutti i clienti del trainer autenticato
    public function index(Request $request)
    {
        $trainer = $request->user();
        $clients = $trainer->clients()->with('user')->get();

        return response()->json($clients);
    }

    // Crea un nuovo utente con ruolo client e lo collega al trainer
    public function store(Request $request)
    {
        $trainer = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'birthdate' => 'nullable|date',
            'goal' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Crea l'utente (user)
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'client',
            'phone_number' => $data['phone_number'] ?? null,
            'gender' => $data['gender'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
        ]);

        // Crea il profilo client
        $client = Client::create([
            'user_id' => $user->id,
            'trainer_id' => $trainer->id,
            'goal' => $data['goal'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json($client->load('user'), 201);
    }

    // Mostra i dati di un cliente
    public function show(Request $request, $id)
    {
        $trainer = $request->user();
        $client = Client::with('user')->where('trainer_id', $trainer->id)->findOrFail($id);

        return response()->json($client);
    }

    // Aggiorna dati client e/o utente associato
    public function update(Request $request, $id)
    {
        $trainer = $request->user();
        $client = Client::where('trainer_id', $trainer->id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($client->user_id)],
            'phone_number' => 'nullable|string',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'birthdate' => 'nullable|date',
            'goal' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Aggiorna utente
        $client->user->update([
            'name' => $data['name'] ?? $client->user->name,
            'email' => $data['email'] ?? $client->user->email,
            'phone_number' => $data['phone_number'] ?? $client->user->phone_number,
            'gender' => $data['gender'] ?? $client->user->gender,
            'birthdate' => $data['birthdate'] ?? $client->user->birthdate,
        ]);

        // Aggiorna profilo client
        $client->update([
            'goal' => $data['goal'] ?? $client->goal,
            'notes' => $data['notes'] ?? $client->notes,
        ]);

        return response()->json($client->load('user'));
    }

    // Elimina cliente e utente associato
    public function destroy(Request $request, $id)
    {
        $trainer = $request->user();
        $client = Client::where('trainer_id', $trainer->id)->findOrFail($id);

        $client->user()->delete(); // elimina anche user
        $client->delete();         // elimina client

        return response()->json(['message' => 'Cliente eliminato.']);
    }
}
