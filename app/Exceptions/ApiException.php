<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Classe de base pour toutes les exceptions API
 */
abstract class ApiException extends Exception
{
    protected int $httpStatusCode;
    protected array $details = [];

    public function __construct(string $message = "", int $httpStatusCode = 400, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Retourne le code d'erreur pour l'API
     */
    abstract public function getErrorCode(): string;

    /**
     * Retourne le code HTTP approprié
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Retourne les détails supplémentaires de l'erreur
     */
    public function getErrorDetails(): array
    {
        return $this->details;
    }

    /**
     * Définit les détails de l'erreur
     */
    protected function setDetails(array $details): void
    {
        $this->details = $details;
    }

    /**
     * Rend la réponse JSON pour l'API
     */
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'details' => $this->getErrorDetails()
            ]
        ], $this->getHttpStatusCode());
    }
}
