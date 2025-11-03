<?php

namespace App\Exceptions;

class NumeroCompteAlreadyExistsException extends ApiException
{
    protected string $numeroCompte;

    public function __construct(string $numeroCompte, ?string $message = null)
    {
        $this->numeroCompte = $numeroCompte;
        $message = $message ?? "Le numéro de compte '{$numeroCompte}' existe déjà";

        parent::__construct($message, 409);
        $this->setDetails(['numeroCompte' => $numeroCompte]);
    }

    public function getNumeroCompte(): string
    {
        return $this->numeroCompte;
    }

    public function getErrorCode(): string
    {
        return 'NUMERO_COMPTE_ALREADY_EXISTS';
    }
}
