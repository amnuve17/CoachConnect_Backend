<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Restituisce i dati dell’utente autenticato (trainer o cliente).
     */
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'clientProfile',
            'clients.user',  // solo se è un trainer
            'notifications'
        ]);

        return response()->json($user);
    }
}
