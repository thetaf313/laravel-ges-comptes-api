<?php

namespace App\Exceptions;

class CompteNotFoundException extends ApiException
{
    protected string $compteId;

    public function __construct(string $compteId, ?string $message = null)
    {
        $this->compteId = $compteId;
        $message = $message ?? "Le compte avec l'ID '{$compteId}' n'existe pas";

        parent::__construct($message, 404);
        $this->setDetails(['compteId' => $compteId]);
    }

    public function getCompteId(): string
    {
        return $this->compteId;
    }

    public function getErrorCode(): string
    {
        return 'COMPTE_NOT_FOUND';
    }
}