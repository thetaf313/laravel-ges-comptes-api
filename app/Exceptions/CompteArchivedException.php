<?php

namespace App\Exceptions;

class CompteArchivedException extends ApiException
{
    protected string $compteId;

    public function __construct(string $compteId, string $message = null)
    {
        $this->compteId = $compteId;
        $message = $message ?? "Impossible de modifier le compte '{$compteId}' car il est archivé";

        parent::__construct($message, 400);
        $this->setDetails(['compteId' => $compteId]);
    }

    public function getCompteId(): string
    {
        return $this->compteId;
    }

    public function getErrorCode(): string
    {
        return 'COMPTE_ARCHIVED';
    }
}
