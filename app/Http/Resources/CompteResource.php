<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_compte' => $this->numero_compte,
            'titulaire' => $this->titulaire,
            'type' => $this->type,
            'solde' => $this->solde,
            'devise' => $this->devise,
            'date_creation' => $this->date_creation,
            'statut' => $this->statut,
            'derniere_modification' => $this->derniere_modification,
            'version' => $this->version,
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'nom' => $this->client->nom,
                    'prenom' => $this->client->prenom,
                    'email' => $this->client->email,
                    'telephone' => $this->client->telephone,
                    'cni' => $this->client->cni,
                    'adresse' => $this->client->adresse,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
