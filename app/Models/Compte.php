<?php

namespace App\Models;

use App\Services\CompteService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = ['solde'];

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
                $model->numero_compte = app(CompteService::class)->generateAccountNumber();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 Scopes de filtrage
    |--------------------------------------------------------------------------
    */

    public function scopeFilterByType($query, $type)
    {
        if (!empty($type)) {
            $query->where('type', $type);
        }
        return $query;
    }

    public function scopeFilterByStatut($query, $statut)
    {
        if (!empty($statut)) {
            $query->where('statut', $statut);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                })->orWhere('numero_compte', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function scopeSort($query, $sort, $order)
    {
        $sort = $sort ?: 'created_at';
        $order = in_array(strtolower($order), ['asc', 'desc']) ? $order : 'desc';
        return $query->orderBy($sort, $order);
    }

    public function scopePaginateLimit($query, $limit)
    {
        $limit = min($limit ?: 10, 100);
        return $query->paginate($limit);
    }


    /** 🔗 Relation avec Client */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /** 🔗 Relation avec les transactions */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /** 🔗 Attributs */
    protected $fillable = [
        'client_id',
        'numero_compte',
        'titulaire',
        'type',
        'solde_initial',
        'devise',
        'date_creation',
        'statut',
        'metadonnees',
        'date_fermeture',
        'motifBlocage',
        'dateBlocage',
        'dateDeblocagePrevue',
        'motifDeblocage',
        'dateDeblocage',
    ];

    protected $casts = [
        'metadonnees' => 'array',
        'date_creation' => 'datetime',
        'solde_intitial' => 'decimal:2',
        'dateBlocage' => 'datetime',
        'dateDeblocagePrevue' => 'datetime',
        'dateDeblocage' => 'datetime',
        'date_fermeture' => 'datetime',
    ];

    public function getMetadonneesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setMetadonneesAttribute($value)
    {
        $this->attributes['metadonnees'] = json_encode($value);
    }

    public function getSoldeAttribute()
    {
        try {
            $service = app(CompteService::class);
            return $service->calculerSolde($this);
        } catch (\Exception $e) {
            Log::error('Erreur calcul solde: ' . $e->getMessage());
            return 0.0;
        }
    }
}
