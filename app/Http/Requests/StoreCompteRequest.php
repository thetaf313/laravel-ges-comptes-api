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
        return [
            'client_id' => 'required|exists:clients,id',
            'titulaire' => 'required|string|max:255',
            'type' => 'required|in:epargne,cheque',
            'solde_initial' => 'required|numeric|min:0',
            'devise' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'L\'ID du client est requis.',
            'client_id.exists' => 'Le client spécifié n\'existe pas.',
            'titulaire.required' => 'Le nom du titulaire est requis.',
            'type.required' => 'Le type de compte est requis.',
            'type.in' => 'Le type doit être épargne ou chèque.',
            'solde_initial.required' => 'Le solde initial est requis.',
            'solde_initial.numeric' => 'Le solde initial doit être un nombre.',
            'solde_initial.min' => 'Le solde initial doit être positif.',
            'devise.required' => 'La devise est requise.',
        ];
    }
}
