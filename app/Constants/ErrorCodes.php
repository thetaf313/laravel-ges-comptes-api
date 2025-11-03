<?php

namespace App\Constants;

/**
 * Constantes pour les codes d'erreur de l'API
 */
class ErrorCodes
{
    // Erreurs générales
    const INTERNAL_ERROR = 'INTERNAL_ERROR';
    const VALIDATION_ERROR = 'VALIDATION_ERROR';
    const INVALID_UUID_FORMAT = 'INVALID_UUID_FORMAT';
    const DATABASE_ERROR = 'DATABASE_ERROR';

    // Erreurs liées aux comptes
    const COMPTE_NOT_FOUND = 'COMPTE_NOT_FOUND';
    const COMPTE_ARCHIVED = 'COMPTE_ARCHIVED';
    const NUMERO_COMPTE_ALREADY_EXISTS = 'NUMERO_COMPTE_ALREADY_EXISTS';
    const COMPTE_TYPE_INVALID = 'COMPTE_TYPE_INVALID';
    const COMPTE_STATUS_INVALID = 'COMPTE_STATUS_INVALID';

    // Erreurs liées aux clients
    const CLIENT_NOT_FOUND = 'CLIENT_NOT_FOUND';

    // Erreurs d'authentification
    const UNAUTHENTICATED = 'UNAUTHENTICATED';
    const UNAUTHORIZED = 'UNAUTHORIZED';

    // Erreurs de blocage
    const COMPTE_ALREADY_BLOCKED = 'COMPTE_ALREADY_BLOCKED';
    const COMPTE_NOT_BLOCKED = 'COMPTE_NOT_BLOCKED';
    const INVALID_BLOCKING_DURATION = 'INVALID_BLOCKING_DURATION';
}
