<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\Client;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    // Lista i workout di un cliente
    public function index(Request $request)
    {
        $user = $request->user();
    
        // Se l'utente è un client, restituisci solo i suoi workout
        if ($user->role === 'client') {
            return response()->json(
                Workout::where('client_id', $user->id)->get()
            );
        }
    
        // Se è un trainer, può filtrare i workout tramite client_id (passato nella query string)
        if ($user->role === 'trainer') {
            $clientId = $request->query('client_id');
    
            if ($clientId) {
                return response()->json(
                    Workout::where('client_id', $clientId)->get()
                );
            }
    
            // Se non specifica client_id, restituisci tutti i suoi workout per tutti i client associati
            $clientIds = $user->clients()->pluck('id'); // assuming Trainer hasMany Clients
            return response()->json(
                Workout::whereIn('client_id', $clientIds)->get()
            );
        }
    
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Crea un nuovo workout per un cliente
    public function store(Request $request, $clientId)
    {
        $trainer = $request->user();

        $client = Client::where('trainer_id', $trainer->id)->findOrFail($clientId);

        $data = $request->validate([
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $workout = Workout::create([
            'client_id' => $client->id,
            'date' => $data['date'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json($workout, 201);
    }

    // Mostra un workout specifico
    public function show(Request $request, $clientId, $workoutId)
    {
        $trainer = $request->user();

        $client = Client::where('trainer_id', $trainer->id)->findOrFail($clientId);

        $workout = $client->workouts()->findOrFail($workoutId);

        return response()->json($workout);
    }

    // Aggiorna un workout
    public function update(Request $request, $clientId, $workoutId)
    {
        $trainer = $request->user();

        $client = Client::where('trainer_id', $trainer->id)->findOrFail($clientId);
        $workout = $client->workouts()->findOrFail($workoutId);

        $data = $request->validate([
            'date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        $workout->update($data);

        return response()->json($workout);
    }

    // Elimina un workout
    public function destroy(Request $request, $clientId, $workoutId)
    {
        $trainer = $request->user();

        $client = Client::where('trainer_id', $trainer->id)->findOrFail($clientId);
        $workout = $client->workouts()->findOrFail($workoutId);

        $workout->delete();

        return response()->json(['message' => 'Workout eliminato.']);
    }
}
