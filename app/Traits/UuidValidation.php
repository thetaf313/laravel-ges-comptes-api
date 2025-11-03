<?php

namespace App\Traits;

use App\Exceptions\InvalidUuidException;
use Ramsey\Uuid\Uuid;

/**
 * Trait pour la validation des UUIDs
 * Peut être utilisé dans les controllers, services, etc.
 */
trait UuidValidation
{
    /**
     * Valide qu'un ID est un UUID valide
     *
     * @param string $id L'ID à valider
     * @throws InvalidUuidException Si l'ID n'est pas un UUID valide
     */
    protected function validateUuid(string $id): void
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidUuidException("L'ID '{$id}' n'est pas un UUID valide");
        }
    }

    /**
     * Valide qu'une chaîne est un UUID valide (version non-throwing)
     *
     * @param string $id L'ID à valider
     * @return bool True si c'est un UUID valide, false sinon
     */
    protected function isValidUuid(string $id): bool
    {
        return Uuid::isValid($id);
    }

    /**
     * Valide un UUID et retourne une réponse d'erreur structurée si invalide
     *
     * @param string $id L'ID à valider
     * @param string $resourceName Nom de la ressource (ex: 'compte', 'client')
     * @return mixed|null Retourne une réponse d'erreur ou null si valide
     */
    protected function validateUuidOrRespond(string $id, string $resourceName = 'ressource')
    {
        try {
            $this->validateUuid($id);
            return null; // UUID valide
        } catch (InvalidUuidException $e) {
            // Si le trait est utilisé dans un controller avec RestResponse
            if (method_exists($this, 'structuredErrorResponse')) {
                return $this->structuredErrorResponse(
                    'INVALID_UUID_FORMAT',
                    "Le format de l'ID de {$resourceName} n'est pas valide",
                    ["{$resourceName}Id" => $id],
                    400
                );
            }

            // Fallback si pas de méthode structuredErrorResponse
            throw $e;
        }
    }
}
