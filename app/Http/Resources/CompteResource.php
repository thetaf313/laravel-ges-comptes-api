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
            'solde_initial' => $this->solde_initial,
            'solde' => $this->solde,
            'devise' => $this->devise,
            'date_creation' => $this->date_creation,
            'statut' => $this->statut,
            'client_id' => $this->client_id,
            'metadata' => $this->metadonnees,
        ];
    }
}
