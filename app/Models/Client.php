<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /** 🔗 Relation avec User */

    public function user()
    {
        return $this->morphOne(User::class, 'authenticatable');
    }

    /** 🔗 Relation avec Compte */

    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }

    /** 🔗 Attributs */

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'date_naissance',
        'adresse',
        'cni',
    ];
}
