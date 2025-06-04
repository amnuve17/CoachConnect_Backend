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

    public function show($id)
    {
        $exercise = Exercise::findOrFail($id);
        return response()->json($exercise);
    }

    // Solo i trainer possono creare
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'muscle_group' => ['required', Rule::in(['chest', 'back', 'legs', 'arms', 'shoulders', 'core', 'full_body'])],
        ]);

        $exercise = Exercise::create($data);

        return response()->json($exercise, 201);
    }

    // Solo i trainer possono aggiornare
    public function update(Request $request, $id)
    {
        $exercise = Exercise::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'muscle_group' => ['sometimes', Rule::in(['chest', 'back', 'legs', 'arms', 'shoulders', 'core', 'full_body'])],
        ]);

        $exercise->update($data);

        return response()->json($exercise);
    }

    // Solo i trainer possono eliminare
    public function destroy($id)
    {
        $exercise = Exercise::findOrFail($id);
        $exercise->delete();

        return response()->json(['message' => 'Esercizio eliminato']);
    }
}
