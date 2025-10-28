<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\AccountCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use App\Services\CompteService;
use App\Traits\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // \Illuminate\Support\Facades\Log::info('Index method called for comptes');
        // $query = Compte::with('client', 'transactions');

        // // Filtres
        // if ($type = $request->get('type')) {
        //     $query->where('type', $type);
        // }

        // if ($statut = $request->get('statut')) {
        //     $query->where('statut', $statut);
        // }

        // if ($search = $request->get('search')) {
        //     $query->whereHas('client', function ($q) use ($search) {
        //         $q->where('nom', 'like', "%{$search}%")
        //             ->orWhere('prenom', 'like', "%{$search}%");
        //     })->orWhere('numero_compte', 'like', "%{$search}%");
        // }

        // // Tri
        // $sort = $request->get('sort', 'created_at');
        // $order = $request->get('order', 'desc');
        // $query->orderBy($sort, $order);

        // // Pagination
        // $limit = min($request->get('limit', 10), 100);
        // $comptes = $query->paginate($limit);

        // return $this->successResponse(
        //     CompteResource::collection($comptes),
        //     'Liste des comptes récupérée avec succès',
        //     $this->paginationData($comptes)
        // );
        Log::info('Index method called for comptes', $request->all());

        $comptes = Compte::with(['client', 'transactions'])
            ->filterByType($request->get('type'))
            ->filterByStatut($request->get('statut'))
            ->search($request->get('search'))
            ->sort($request->get('sort'), $request->get('order'))
            ->paginateLimit($request->get('limit'));

        return $this->successResponse(
            CompteResource::collection($comptes),
            'Liste des comptes récupérée avec succès',
            $this->paginationData($comptes)
        );
    }


    public function store(StoreCompteRequest $request)
    {
        Log::info('📥 Requête reçue dans store()', [
            'method' => $request->method(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'body' => $request->all(),
        ]);

        try {
            $data = $request->validated();

            $client = null;
            $compte = null;

            DB::transaction(function () use ($data, &$client, &$compte) {
                // Gérer la création/récupération du client
                if (isset($data['client']['id']) && !empty($data['client']['id'])) {
                    // Utiliser un client existant
                    $client = Client::with('user')->find($data['client']['id']);
                    if (!$client) {
                        throw new \Exception('Client spécifié non trouvé');
                    }
                    if (!$client->user) {
                        throw new \Exception('Client sans compte utilisateur associé');
                    }
                } else {
                    // Générer mot de passe temporaire et code SMS
                    $password = Str::random(10);
                    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                    // Créer un nouveau client
                    $client = Client::create([
                        'nom' => explode(' ', $data['client']['titulaire'])[0] ?? $data['client']['titulaire'],
                        'prenom' => explode(' ', $data['client']['titulaire'])[1] ?? '',
                        'email' => $data['client']['email'],
                        'telephone' => $data['client']['telephone'],
                        'adresse' => $data['client']['adresse'],
                        'cni' => $data['client']['nci'],
                    ]);

                    $user = User::create([
                        'email' => $data['client']['email'],
                        'password' => bcrypt(Str::random(10)), // Mot de passe temporaire
                        'verification_code' => $code,
                        'code_expires_at' => now()->addHour(24),
                        'authenticatable_type' => Client::class,
                        'authenticatable_id' => $client->id,
                    ]);

                    // Mettre à jour le client avec l'user_id
                    $client->update(['user_id' => $user->id]);
                }

                // Créer le compte
                $compte = Compte::create([
                    'client_id' => $client->id,
                    'numero_compte' => app(CompteService::class)->generateAccountNumber(),
                    'titulaire' => $client->nom . ' ' . $client->prenom,
                    'type' => $data['type'],
                    'solde_initial' => $data['soldeInitial'],
                    'devise' => $data['devise'],
                    'statut' => 'actif',
                    'date_creation' => now(),
                    'metadonnees' => ['derniere_modification' => now(), 'version' => 1],
                ]);

                // Envoyer les notifications
                event(new AccountCreated($client, $password, $code));
            });

            return $this->successResponse(
                new CompteResource($compte),
                'Compte créé avec succès',
                null,
                Response::HTTP_CREATED
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $e->errors(),
                ],
            ], 400);
        } catch (\Throwable $th) {
            Log::error('Erreur création compte: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Erreur côté serveur: ' . $th->getMessage(),
            ], 500);
        }
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
        Log::info('📝 Mise à jour du compte', [
            'compte_id' => $compte->id,
            'data' => $request->all(),
        ]);

        try {
            $data = $request->validated();

            DB::transaction(function () use ($data, $compte) {
                $currentVersion = $compte->metadonnees['version'] ?? 1;
                $updateMetadata = false;

                // Mise à jour du titulaire du compte
                if (isset($data['titulaire'])) {
                    $compte->update([
                        'titulaire' => $data['titulaire'],
                        'metadonnees' => array_merge($compte->metadonnees ?? [], [
                            'derniere_modification' => now(),
                            'version' => $currentVersion + 1,
                        ]),
                    ]);
                    $updateMetadata = true;
                }

                // Mise à jour des informations client
                if (isset($data['informationsClient']) && !empty($data['informationsClient'])) {
                    $clientData = $data['informationsClient'];

                    // Mise à jour du client
                    $updateData = [];
                    if (isset($clientData['telephone'])) {
                        $updateData['telephone'] = $clientData['telephone'];
                    }
                    if (isset($clientData['nci'])) {
                        $updateData['nci'] = $clientData['nci'];
                    }

                    if (!empty($updateData)) {
                        $compte->client->update($updateData);
                        $updateMetadata = true;
                    }

                    // Mise à jour de l'utilisateur (email et password)
                    if (isset($clientData['email']) || isset($clientData['password'])) {
                        $userUpdateData = [];
                        if (isset($clientData['email'])) {
                            $userUpdateData['email'] = $clientData['email'];
                        }
                        if (isset($clientData['password'])) {
                            $userUpdateData['password'] = Hash::make($clientData['password']);
                        }

                        if (!empty($userUpdateData)) {
                            $compte->client->user->update($userUpdateData);
                            $updateMetadata = true;
                        }
                    }
                }

                // Mettre à jour les métadonnées si des changements ont été effectués
                if ($updateMetadata && !isset($data['titulaire'])) {
                    $compte->update([
                        'metadonnees' => array_merge($compte->metadonnees ?? [], [
                            'derniere_modification' => now(),
                            'version' => $currentVersion + 1,
                        ]),
                    ]);
                }
            });

            // Recharger le compte avec les relations mises à jour
            $compte->load('client.user');

            return $this->successResponse(
                new CompteResource($compte),
                'Compte mis à jour avec succès'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $e->errors(),
                ],
            ], 400);
        } catch (\Throwable $th) {
            Log::error('Erreur mise à jour compte: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Erreur côté serveur: ' . $th->getMessage(),
            ], 500);
        }
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
