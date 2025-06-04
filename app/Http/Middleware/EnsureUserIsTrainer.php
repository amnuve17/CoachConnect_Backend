<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTrainer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'trainer') {
            return response()->json(['error' => 'Accesso riservato ai trainer.'], 403);
        }

        return $next($request);
    }
}
