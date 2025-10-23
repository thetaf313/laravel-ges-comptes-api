<?php

namespace App\Http\Requests;

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
            'type' => 'required|in:epargne,cheque',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string',
            'solde' => 'required|numeric|min:10000',
            'client' => 'required|array',
            'client.id' => 'nullable|exists:clients,id',
            'client.titulaire' => 'required|string|max:255',
            'client.nci' => 'required|regex:/^[12]\d{12}$/|unique:clients,cni',
            'client.email' => 'required|email|unique:clients,email',
            'client.telephone' => 'required|regex:/^(\+221)?7[05678]\d{7}$/|unique:clients,telephone',
            'client.adresse' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10 000 FCFA.',
            'client.telephone.regex' => 'Le numéro doit être un téléphone mobile sénégalais valide.',
        ];
    }
}
