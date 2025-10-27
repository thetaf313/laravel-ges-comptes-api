<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /** 🔗 Relations */
    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    /** 🔗 Attributs */
    protected $fillable = [
        'compte_id',
        'montant',
        'type',
        'date',
        'description',
    ];

}
