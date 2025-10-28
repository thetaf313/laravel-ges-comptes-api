<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidCni;
use App\Rules\ValidTelephoneSenegalais;

class UpdateCompteRequest extends FormRequest
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
            'titulaire' => 'nullable|string|max:255',
            'informationsClient' => 'nullable|array',
            'informationsClient.telephone' => ['nullable', 'unique:clients,telephone', new ValidTelephoneSenegalais],
            'informationsClient.email' => 'nullable|email|unique:users,email',
            'informationsClient.password' => 'nullable|string|min:8',
            'informationsClient.nci' => ['nullable', new ValidCni],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier qu'au moins un champ est fourni
            $titulaire = $this->input('titulaire');
            $infosClient = $this->input('informationsClient', []);

            $hasTitulaire = !empty($titulaire);
            $hasClientInfo = !empty(array_filter($infosClient));

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ doit être fourni pour la modification.');
            }
        });
    }
}
