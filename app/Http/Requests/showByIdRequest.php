<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class showByIdRequest extends FormRequest
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
            'id' => 'required|uuid|exists:comptes,id',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => "L'identifiant du compte est requis.",
            'id.uuid' => "L'identifiant du compte doit être un UUID valide."
        ];
    }
}
