<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Support\Str;


class CompteService
{

    public function generateAccountNumber(): string
    {
        do {
            $number = 'CPT-' . strtoupper(Str::random(8));
        } while (Compte::where('numero_compte', $number)->exists());

        return $number;
    }

    public function calculerSolde(Compte $compte): float
    {
        if ($compte->relationLoaded('transactions')) {
            $depots = $compte->transactions->where('type', 'depot')->where('statut', 'validee')->sum('montant');
            $retraits = $compte->transactions->where('type', 'retrait')->where('statut', 'validee')->sum('montant');
            $frais = $compte->transactions->where('type', 'frais')->where('statut', 'validee')->sum('montant');
        } else {
            $depots = Transaction::where('compte_id', $compte->id)->where('type', 'depot')->where('statut', 'validee')->sum('montant');
            $retraits = Transaction::where('compte_id', $compte->id)->where('type', 'retrait')->where('statut', 'validee')->sum('montant');
            $frais = Transaction::where('compte_id', $compte->id)->where('type', 'frais')->where('statut', 'validee')->sum('montant');
        }

        return $compte->solde_initial + $depots - $retraits - $frais;
    }
}
