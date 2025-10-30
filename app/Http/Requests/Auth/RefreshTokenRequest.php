<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'utilisateur doit être authentifié pour rafraîchir son token
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => [
                'required',
                'string',
                'min:1',
                'max:1000'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'refresh_token.required' => 'Le token de rafraîchissement est obligatoire.',
            'refresh_token.string' => 'Le token de rafraîchissement doit être une chaîne de caractères.',
            'refresh_token.min' => 'Le token de rafraîchissement ne peut pas être vide.',
            'refresh_token.max' => 'Le token de rafraîchissement est trop long.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'refresh_token' => 'token de rafraîchissement',
        ];
    }
}
