<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'required|regex:/^(\+221)?7[05678]\d{7}$/|unique:clients,telephone',
            'date_naissance' => 'required|date|before:today',
            'adresse' => 'required|string',
            'cni' => 'required|regex:/^[12]\d{12}$/|unique:clients,cni'
        ];
    }
}
