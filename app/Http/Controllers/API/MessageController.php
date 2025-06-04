<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Mostra i messaggi dell'utente autenticato (inviati o ricevuti)
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }

    // Invia un nuovo messaggio
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content'],
        ]);

        return response()->json($message, 201);
    }

    // Segna un messaggio come letto
    public function markAsRead($id)
    {
        $user = Auth::user();

        $message = Message::where('id', $id)
            ->where('receiver_id', $user->id)
            ->firstOrFail();

        $message->update(['read_at' => now()]);

        return response()->json(['message' => 'Messaggio segnato come letto.']);
    }
}
