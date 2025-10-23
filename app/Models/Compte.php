<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'numero_compte',
        'titulaire',
        'type',
        'solde',
        'devise',
        'date_creation',
        'statut',
        'derniere_modification',
        'version'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            // Génération automatique du numéro de compte
            if (empty($model->numero_compte)) {
                // $model->numero_compte = 'CPT-' . strtoupper(Str::random(8));
                $model->numero_compte = self::generateAccountNumber();
            }
        });

        
    }

    protected static function generateAccountNumber(): string
    {
        do {
            $number = 'CPT-' . strtoupper(Str::random(8));
        } while (self::where('numero_compte', $number)->exists());

        return $number;
    }


    /** 🔗 Relation avec Client */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
