<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Intercepte la requête entrante et enregistre les opérations importantes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exécuter la requête et obtenir la réponse
        $response = $next($request);

        try {
            $route = $request->route();
            $routeName = $route?->getName() ?? '';
            $path = $route?->uri() ?? $request->path();

            // Exemple : journaliser toutes les opérations POST sur comptes
            if (
                $request->isMethod('post')
                && str_contains($path, 'comptes')
            ) {
                Log::info('🧾 Opération de création de compte détectée', [
                    'date_heure'     => now()->toISOString(),
                    'host'           => $request->getHost(),
                    'nom_operation'  => 'Création de compte',
                    'ressource'      => 'Compte',
                    'user_agent'     => $request->userAgent(),
                    'ip'             => $request->ip(),
                    'route'          => $routeName ?: $path,
                    'status_code'    => $response->getStatusCode(),
                ]);
            }

            // Tu peux ajouter ici d'autres cas, par exemple :
            // - PUT/PATCH pour mise à jour
            // - DELETE pour suppression
        } catch (\Throwable $th) {
            // On logge l’erreur, mais on ne bloque pas la requête
            Log::error('Erreur dans LoggingMiddleware', [
                'message' => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }

        return $response;
    }
}
