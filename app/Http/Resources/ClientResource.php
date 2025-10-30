<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->whenLoaded('user', fn() => $this->user->email),
            'telephone' => $this->telephone,
            'date_naissance' => $this->date_naissance ? \Carbon\Carbon::parse($this->date_naissance)->toISOString() : null,
            'adresse' => $this->adresse,
            'cni' => $this->cni,
        ];
    }
}
