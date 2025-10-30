<?php

namespace App\DTOs;

/**
 * DTO pour contenir les données nécessaires aux notifications
 * lors de la création d'un compte client
 */
class ClientNotificationData
{
    public function __construct(
        public string $nom,
        public string $prenom,
        public string $email,
        public string $telephone,
        public string $password,
        public string $code,
        public string $numeroCompte,
        public string $userId,
        public string $adresse = '',
        public string $cni = ''
    ) {}

    /**
     * Créer une instance depuis les données du formulaire, le client créé et le numéro de compte
     */
    public static function fromClientAndFormData(\App\Models\Client $client, array $clientData, string $password, string $code, string $numeroCompte): self
    {
        return new self(
            nom: $client->nom,
            prenom: $client->prenom,
            email: $client->user->email,
            telephone: $client->telephone,
            password: $password,
            code: $code,
            numeroCompte: $numeroCompte,
            userId: $client->user->id,
            adresse: $client->adresse ?? '',
            cni: $client->cni ?? ''
        );
    }

    /**
     * Obtenir le nom complet
     */
    public function getNomComplet(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }
}
