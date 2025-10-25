<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Models\Compte;
use App\Traits\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="Ges-Comptes API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 * @OA\Schema(
 *     schema="CompteResource",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="uuid"),
 *     @OA\Property(property="numero_compte", type="string", example="CPT-ABC123"),
 *     @OA\Property(property="titulaire", type="string", example="John Doe"),
 *     @OA\Property(property="type", type="string", enum={"epargne", "cheque"}),
 *     @OA\Property(property="solde", type="number", format="float", example=1000.50),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="date_creation", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}),
 *     @OA\Property(property="derniere_modification", type="string", format="date-time"),
 *     @OA\Property(property="version", type="integer", example=1),
 *     @OA\Property(property="client", ref="#/components/schemas/Client"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="Client",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="uuid"),
 *     @OA\Property(property="nom", type="string", example="Doe"),
 *     @OA\Property(property="prenom", type="string", example="John"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="telephone", type="string", example="+221771234567"),
 *     @OA\Property(property="cni", type="string", example="123456789012"),
 *     @OA\Property(property="adresse", type="string")
 * )
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     @OA\Property(property="currentPage", type="integer", example=1),
 *     @OA\Property(property="totalPages", type="integer", example=5),
 *     @OA\Property(property="totalItems", type="integer", example=50),
 *     @OA\Property(property="itemsPerPage", type="integer", example=10),
 *     @OA\Property(property="hasNext", type="boolean", example=true),
 *     @OA\Property(property="hasPrevious", type="boolean", example=false)
 * )
 */
class CompteController extends Controller
{
    use RestResponse;

    /**
     * GET /api/v1/comptes
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister tous les comptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte (epargne, cheque)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut (actif, bloque, ferme)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Rechercher par nom/prénom du titulaire ou numéro de compte",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri (asc, desc)",
     *         required=false,
     *         @OA\Schema(type="string", default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *         )
     *     )
     * )
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

    /**
     * Afficher un compte spécifique
     * @OA\Get(
     *     path="/api/v1/comptes/{compte}",
     *     summary="Afficher les détails d'un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show(Compte $compte)
    {
        return $this->successResponse(
            new CompteResource($compte->load('client')),
            'Détails du compte récupérés'
        );
    }

    public function update(UpdateCompteRequest $request, Compte $compte)
    {
        $data = $request->validated();

        $compte->update($data);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte mis à jour avec succès'
        );
    }

    public function destroy(Compte $compte)
    {
        $compte->delete();

        return $this->successResponse(
            null,
            'Compte supprimé avec succès'
        );
    }
}
