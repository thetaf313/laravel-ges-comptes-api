<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    public function isClient()
    {
        return $this->authenticatable_type === Client::class;
    }

    public function isAdmin()
    {
        return $this->authenticatable_type === Admin::class;
    }

    public function getRoleAttribute()
    {
        if ($this->isAdmin()) {
            return 'admin';
        } elseif ($this->isClient()) {
            return 'client';
        } else {
            return 'guest';
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'authenticatable_type',
        'authenticatable_id',
        'verification_code',
        'code_expires_at',
        'is_active',  // Nouveau : true si actif, false sinon
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'code_expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /** 🔗 Relation polymorphe */
    public function authenticatable()
    {
        return $this->morphTo();
    }

    /** 🔗 Relations Passport */
    public function oauthAccessTokens()
    {
        return $this->hasMany(\Laravel\Passport\Token::class, 'user_id');
    }

    public function oauthClients()
    {
        return $this->hasMany(\Laravel\Passport\Client::class, 'user_id');
    }

    /**
     * Définir les scopes disponibles pour cet utilisateur
     */
    public function getScopes()
    {
        // Pour l'instant, pas de scopes pour éviter les erreurs de validation
        return [];
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role)
    {
        // Pour l'instant, on utilise une logique simple basée sur l'email
        // En production, il faudrait une table roles et user_roles
        return str_contains($this->email, 'admin') || str_contains($this->email, 'manager');
    }
}
