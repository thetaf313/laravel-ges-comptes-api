<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Models\Compte;
use App\Traits\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompteController extends Controller
{
    use RestResponse;

    /**
     * GET /api/v1/comptes
     */
    public function index(Request $request)
    {
        $query = Compte::with('client');

        // Filtres
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            })->orWhere('numero_compte', 'like', "%{$search}%");
        }

        // Tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
        $limit = min($request->get('limit', 10), 100);
        $comptes = $query->paginate($limit);

        return $this->successResponse(
            CompteResource::collection($comptes),
            'Liste des comptes récupérée avec succès',
            $this->paginationData($comptes)
        );
    }

    /**
     * GET /api/v1/clients/{id}/comptes
     */
    public function comptesByClient($id)
    {
        $comptes = Compte::where('client_id', $id)->paginate(10);

        return $this->successResponse(
            CompteResource::collection($comptes),
            "Comptes du client {$id} récupérés avec succès",
            $this->paginationData($comptes)
        );
    }

    public function store(StoreCompteRequest $request)
    {
        $data = $request->validated();
        $clientData = $data['client'];

        // 1️⃣ Vérifier ou créer le client
        $client = Client::where('email', $clientData['email'])
            ->orWhere('telephone', $clientData['telephone'])
            ->first();

        if (!$client) {
            $password = Str::random(10);
            $code = rand(100000, 999999);

            $client = Client::create([
                'nom_complet' => $clientData['titulaire'],
                'nci' => $clientData['nci'],
                'email' => $clientData['email'],
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
                'password' => Hash::make($password),
                'code' => $code,
            ]);

            // 👉 Envoyer email et SMS
            // dispatch(new SendWelcomeEmailJob($client, $password));
            // dispatch(new SendCodeSMSJob($client->telephone, $code));
        }

        // 2️⃣ Créer le compte
        $numeroCompte = 'C00' . rand(10000, 99999);
        $compte = Compte::create([
            'client_id' => $client->id,
            'numero_compte' => $numeroCompte,
            'type' => $data['type'],
            'solde' => $data['soldeInitial'],
            'devise' => $data['devise'],
            'statut' => 'actif',
        ]);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte créé avec succès'
        );
    }
}
