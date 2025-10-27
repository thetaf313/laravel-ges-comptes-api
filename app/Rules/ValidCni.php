<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCni implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Votre algorithme personnalisé pour valider le CNI
        // Exemple : Vérifier le format et un checksum simple
        if (!preg_match('/^[12]\d{12}$/', $value)) {
            $fail('Le CNI doit commencer par 1 ou 2 et contenir exactement 13 chiffres.');
            return;
        }
    }
}
