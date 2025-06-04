<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Tutti gli utenti vedono le proprie notifiche
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)->latest()->get();
        return response()->json($notifications);
    }

    // Solo i trainer possono creare notifiche per i propri client
    public function store(Request $request)
    {
        $trainer = $request->user();

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        if (!$trainer->clients()->where('user_id', $data['user_id'])->exists()) {
            return response()->json(['error' => 'Non puoi inviare notifiche a questo utente.'], 403);
        }

        $notification = Notification::create([
            'user_id' => $data['user_id'],
            'message' => $data['message'],
        ]);

        return response()->json($notification, 201);
    }

    // Utente segna una sua notifica come letta
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifica letta']);
    }

    // Solo trainer può eliminare le notifiche dei propri client
    public function destroy(Request $request, $id)
    {
        $trainer = $request->user();

        $notification = Notification::findOrFail($id);

        if (!$trainer->clients()->where('user_id', $notification->user_id)->exists()) {
            return response()->json(['error' => 'Non puoi eliminare questa notifica.'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notifica eliminata']);
    }
}
