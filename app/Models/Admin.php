<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
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

    /** 🔗 Relation avec User */
    public function user()
    {
        return $this->morphOne(User::class, 'authenticatable');
    }

    /** 🔗 Attributs */
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
    ];

}
