<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    // Tutti possono vedere gli esercizi
    public function index()
    {
        return response()->json(Exercise::all());
    }

    public function show(Request $request, $id) {
        $user = $request->user();
        $workout = Workout::with('client.user')->findOrFail($id);
    
        // Permesso: trainer o client solo se autorizzato
        if ($user->role === 'trainer' && $workout->client->trainer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'client' && $workout->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        return response()->json($workout);
    }

    // Solo i trainer possono creare
    public function store(Request $request) {
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
        $client = Client::where('id', $data['client_id'])->where('trainer_id', $trainer->id)->firstOrFail();
    
        $workout = Workout::create([
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

    // Solo i trainer possono aggiornare
    public function update(Request $request, $id) {
        $user = $request->user();
        $workout = Workout::with('client.user')->findOrFail($id);
    
        // Permesso...
        if ($user->role === 'trainer' && $workout->client->trainer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'client' && $workout->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'notes' => 'nullable|string',
            // ... altri campi se servono
        ]);
        $workout->update($data);
        return response()->json($workout);
    }

    // Solo i trainer possono eliminare
    public function destroy(Request $request, $id) {
        $user = $request->user();
        $workout = Workout::with('client.user')->findOrFail($id);
    
        // Permesso...
        if ($user->role === 'trainer' && $workout->client->trainer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'client' && $workout->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $workout->delete();
        return response()->json(['message' => 'Workout eliminato.']);
    }
}
