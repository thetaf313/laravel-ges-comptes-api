<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTelephoneSenegalais implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer le numéro (supprimer espaces, tirets, etc.)
        $cleaned = preg_replace('/\D/', '', $value);

        // Vérifier si c'est un numéro sénégalais
        if (!preg_match('/^\+2217[05678]\d{7}$/', $cleaned) && !preg_match('/^7[05678]\d{7}$/', $cleaned)) {
            $fail('Le numéro doit être un téléphone mobile sénégalais valide (ex: +221 77 123 45 67).');
            return;
        }

        // Votre algorithme personnalisé (ex: vérifier l'opérateur)
        $prefix = substr($cleaned, -9, 2);  // Les 2 chiffres après 221 ou directement
        $operators = [
            '70' => 'Expresso',
            '75' => 'Promobile',
            '76' => 'Tigo',
            '77' => 'Orange',
            '78' => 'Free'
        ];
        if (!array_key_exists($prefix, $operators)) {
            $fail('L\'opérateur téléphonique n\'est pas reconnu.');
        }
    }
}
