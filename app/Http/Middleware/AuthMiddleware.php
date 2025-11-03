<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Vérifie si l'utilisateur est authentifié via Passport.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si pas de header Authorization, essayer de lire depuis les cookies
        if (!$request->hasHeader('Authorization') && $request->hasCookie('access_token')) {
            $token = $request->cookie('access_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        if (!Auth::guard('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Token d\'authentification manquant ou invalide',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        return $next($request);
    }
}
