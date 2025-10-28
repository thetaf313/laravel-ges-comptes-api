<?php

namespace App\Http\Requests;

use App\Rules\ValidCni;
use App\Rules\ValidTelephoneSenegalais;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'type' => 'required|in:epargne,cheque',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string',
            'client' => 'required|array',
            'client.id' => 'nullable|exists:clients,id',
        ];

        // Si client.id est fourni, les autres champs ne sont pas requis
        if ($this->input('client.id')) {
            // Client existant - aucun autre champ requis
        } else {
            // Nouveau client - tous les champs requis
            $rules = array_merge($rules, [
                'client.titulaire' => 'required|string|max:255',
                'client.nci' => ['required', new ValidCni],
                'client.email' => 'required|email|unique:users,email',
                'client.telephone' => ['required', new ValidTelephoneSenegalais, 'unique:clients,telephone'],
                'client.adresse' => 'required|string',
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'type.required' => 'Le type de compte est requis.',
            'type.in' => 'Le type doit être épargne ou chèque.',
            'soldeInitial.required' => 'Le solde initial est requis.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être positif.',
            'devise.required' => 'La devise est requise.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde doit être positif.',
            'client.required' => 'Les informations du client sont requises.',
            'client.id.exists' => 'Le client spécifié n\'existe pas.',
            'client.titulaire.required' => 'Le nom du titulaire est requis.',
            'client.nci' => 'Le CNI n\'est pas valide.',
            'client.email.required' => 'L\'email est requis.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le téléphone est requis.',
            'client.telephone.unique' => 'Ce téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est requise.',
        ];
    }
}
