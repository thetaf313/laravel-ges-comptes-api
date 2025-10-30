<?php

namespace App\Exceptions;

use Exception;

class InvalidUuidException extends ApiException
{
    public function __construct(string $message = "L'ID fourni n'est pas un UUID valide", array $details = [])
    {
        parent::__construct($message, 400, 0, null);
        $this->setDetails($details);
    }

    public function getErrorCode(): string
    {
        return 'INVALID_UUID_FORMAT';
    }
}
