<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\Client;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'client') {
            $client = $user->clientProfile; // <--- usa la relazione giusta!
            if (!$client) {
                return response()->json([]); // Nessun client associato
            }
            $workouts = Workout::with(['client.user', 'exercises'])
                ->where('client_id', $client->id)
                ->get();
        
            $workouts = $workouts->map(function ($workout) {
                return [
                    'id' => $workout->id,
                    'title' => $workout->title ?? '',
                    'notes' => $workout->notes,
                    'date' => $workout->date,
                    'start_date' => $workout->start_date ?? null,
                    'end_date' => $workout->end_date ?? null,
                    'client_id' => $workout->client_id,
                    'client_name' => $workout->client?->user?->name ?? '',
                    'exercises' => $workout->exercises ?? [],
                ];
            });
        
            return response()->json($workouts);
        }

        // Se è un trainer
        if ($user->role === 'trainer') {
            $clientId = $request->query('client_id');

            $query = \App\Models\Workout::with('client.user');

            if ($clientId) {
                $query->where('client_id', $clientId);
            } else {
                $clientIds = $user->clients()->pluck('id');
                $query->whereIn('client_id', $clientIds);
            }

            $workouts = $query->get();

            $workouts = $workouts->map(function ($workout) {
                return [
                    'id' => $workout->id,
                    'title' => $workout->title ?? '',
                    'notes' => $workout->notes,
                    'date' => $workout->date,
                    'start_date' => $workout->start_date ?? null,
                    'end_date' => $workout->end_date ?? null,
                    'client_id' => $workout->client_id,
                    'client_name' => $workout->client?->user?->name ?? '',
                ];
            });

            return response()->json($workouts);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
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
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Prendi il workout con il client associato
        $workout = Workout::with('client')->findOrFail($id);

        // Permesso: trainer può modificare solo i workout dei suoi client
        if ($user->role === 'trainer' && $workout->client->trainer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'notes' => 'nullable|string',
            // se vuoi aggiornare altri campi, aggiungili qui
        ]);

        $workout->update($data);

        return response()->json($workout);
    }

    // Elimina un workout
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $workout = Workout::with('client')->findOrFail($id);

        if ($user->role === 'trainer' && $workout->client->trainer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $workout->delete();

        return response()->json(['message' => 'Workout eliminato.']);
    }

    public function store(Request $request)
    {
        $trainer = $request->user();
    
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'exercises' => 'required|array|min:1',
            'exercises.*.name' => 'required|string|max:255',
            'exercises.*.sets' => 'required|integer|min:1',
            'exercises.*.reps' => 'required|integer|min:1',
            'exercises.*.rest_seconds' => 'required|integer|min:0',
            'exercises.*.notes' => 'nullable|string',
        ]);
    
        // Controlla che il client sia del trainer loggato
        $client = \App\Models\Client::where('id', $data['client_id'])
            ->where('trainer_id', $trainer->id)
            ->firstOrFail();
    
        $workout = \App\Models\Workout::create([
            'client_id' => $client->id,
            'date' => $data['date'],
            'notes' => $data['notes'] ?? null,
            'title' => $data['title']
        ]);
    
        foreach ($data['exercises'] as $exerciseData) {
            $workout->exercises()->create($exerciseData);
        }
    
        return response()->json($workout->load('exercises'), 201);
    }
    
}
