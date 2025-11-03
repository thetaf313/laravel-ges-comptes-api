<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log des erreurs non gérées
            if ($this->shouldReport($e)) {
                Log::error('Unhandled exception', [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Gestion spécifique pour les requêtes API
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Gestion des exceptions pour les requêtes API
     */
    private function handleApiException(Request $request, Throwable $exception)
    {
        // Utiliser la méthode render() des ApiException si disponible
        if ($exception instanceof ApiException) {
            return $exception->render($request);
        }

        // Gestion des autres types d'exceptions
        return match (true) {
            $exception instanceof ValidationException => $this->handleValidationException($exception),
            $exception instanceof ModelNotFoundException => $this->handleModelNotFoundException($exception),
            $exception instanceof AuthenticationException => $this->handleAuthenticationException($exception),
            $exception instanceof AuthorizationException => $this->handleAuthorizationException($exception),
            $exception instanceof QueryException => $this->handleQueryException($exception),
            $exception instanceof NotFoundHttpException => $this->handleNotFoundHttpException(),
            $exception instanceof MethodNotAllowedHttpException => $this->handleMethodNotAllowedException(),
            default => $this->handleGenericException($exception)
        };
    }

    private function handleValidationException(ValidationException $exception)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Données de validation invalides',
                'details' => $exception->errors()
            ]
        ], 422);
    }

    private function handleModelNotFoundException(ModelNotFoundException $exception)
    {
        $model = class_basename($exception->getModel());

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'RESOURCE_NOT_FOUND',
                'message' => "La ressource {$model} demandée n'existe pas",
                'details' => [
                    'model' => $model,
                    'ids' => $exception->getIds()
                ]
            ]
        ], 404);
    }

    private function handleAuthenticationException(AuthenticationException $exception)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentification requise'
            ]
        ], 401);
    }

    private function handleAuthorizationException(AuthorizationException $exception)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Accès non autorisé'
            ]
        ], 403);
    }

    private function handleQueryException(QueryException $exception)
    {
        // Log de l'erreur pour debug
        Log::error('Database query error', [
            'error' => $exception->getMessage(),
            'sql' => $exception->getSql(),
            'bindings' => $exception->getBindings()
        ]);

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'DATABASE_ERROR',
                'message' => 'Erreur lors de l\'accès à la base de données'
            ]
        ], 500);
    }

    private function handleNotFoundHttpException()
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'ROUTE_NOT_FOUND',
                'message' => 'Route non trouvée'
            ]
        ], 404);
    }

    private function handleMethodNotAllowedException()
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'METHOD_NOT_ALLOWED',
                'message' => 'Méthode HTTP non autorisée'
            ]
        ], 405);
    }

    private function handleGenericException(Throwable $exception)
    {
        // En développement, montrer plus de détails
        $details = [];
        if (app()->environment('local')) {
            $details = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => app()->environment('local') ? $exception->getMessage() : 'Erreur interne du serveur',
                'details' => $details
            ]
        ], 500);
    }
}
