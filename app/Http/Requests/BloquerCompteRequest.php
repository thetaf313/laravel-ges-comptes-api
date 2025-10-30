<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BloquerCompteRequest extends FormRequest
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
            'dateBlocage' => 'required|date',
            'motif' => 'required|string|max:255',
            'duree' => 'required|integer|min:1|max:365',
            // accept both singular and plural forms for minute
            'unite' => 'required|in:minute,minutes,jours,semaines,mois,annees',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dateBlocage.required' => 'La date de début de blocage est requise.',
            'dateBlocage.date' => 'La date de début de blocage doit être une date valide.',
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.',
            'duree.required' => 'La durée de blocage est obligatoire.',
            'duree.integer' => 'La durée doit être un nombre entier.',
            'duree.min' => 'La durée doit être d\'au moins 1.',
            'duree.max' => 'La durée ne peut pas dépasser 365.',
            'unite.required' => 'L\'unité de temps est obligatoire.',
            'unite.in' => 'L\'unité doit être "minute(s)", "jours", "semaines", "mois" ou "annees".',
        ];
    }
}
