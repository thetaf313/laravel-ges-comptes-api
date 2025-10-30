<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\ClientNotificationData;
use App\Events\AccountCreated;
use App\Exceptions\CompteArchivedException;
use App\Exceptions\CompteNotFoundException;
use App\Exceptions\InvalidUuidException;
use App\Exceptions\NumeroCompteAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BloquerCompteRequest;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use App\Services\CompteService;
use App\Traits\RestResponse;
use App\Traits\UuidValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
 *     @OA\Property(property="solde_initial", type="number", format="float", example=10000.00),
 *     @OA\Property(property="solde", type="number", format="float", example=1000.50),
 *     @OA\Property(property="devise", type="string", example="XOF"),
 *     @OA\Property(property="date_creation", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}),
 *     @OA\Property(property="client_id", type="string", example="uuid"),
 *     @OA\Property(property="informations_blocage", type="object", description="Informations de blocage affichées pour les comptes épargne (même si non bloqués actuellement)",
 *         @OA\Property(property="motifBlocage", type="string", example="Blocage pour vérification"),
 *         @OA\Property(property="dateBlocage", type="string", format="date-time"),
 *         @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time"),
 *         @OA\Property(property="motifDeblocage", type="string", nullable=true),
 *         @OA\Property(property="dateDeblocage", type="string", format="date-time", nullable=true)
 *     ),
 *     @OA\Property(property="metadata", type="object"),
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
    use RestResponse, UuidValidation;

    protected CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * GET /api/v1/comptes
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister tous les comptes actifs",
     *     tags={"Comptes"},
     * @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"epargne", "cheque"},
     *             default=""
     *         )
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
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="desc"
     *         )
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
        Log::info('Index method called for comptes', $request->all());

        $comptes = Compte::with(['transactions'])
            ->where('statut', 'actif') // Seuls les comptes actifs sont listés
            ->filterByType($request->get('type'))
            ->search($request->get('search'))
            ->sort($request->get('sort'), $request->get('order'))
            ->paginateLimit($request->get('limit'));

        return $this->successResponse(
            CompteResource::collection($comptes),
            'Liste des comptes actifs récupérée avec succès',
            $this->paginationData($comptes)
        );
    }


    /**
     * POST /api/v1/comptes
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "soldeInitial", "devise", "client"},
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="epargne"),
     *             @OA\Property(property="soldeInitial", type="number", format="float", example=10000.00),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal"),
     *                 @OA\Property(property="nci", type="string", example="123456789012"),
     *                 description="Informations du nouveau client (ou utiliser 'id' pour un client existant)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données de requête invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur côté serveur")
     *         )
     *     )
     * )
     */
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
                $isNewClient = false;
                $password = null;
                $code = null;

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
                    // Chercher d'abord par email dans users
                    $existingUser = User::with('authenticatable')->where('email', $data['client']['email'])->first();

                    if ($existingUser) {
                        // Vérifier que l'utilisateur a un client associé
                        if (!$existingUser->authenticatable instanceof Client) {
                            throw new \Exception('Utilisateur trouvé mais n\'est pas un client');
                        }
                        $client = $existingUser->authenticatable;
                    } else {
                        // Chercher par téléphone ou CNI dans clients
                        $existingClient = Client::with('user')
                            ->where('telephone', $data['client']['telephone'])
                            ->orWhere('cni', $data['client']['nci'])
                            ->first();

                        if ($existingClient) {
                            // Utiliser le client existant
                            $client = $existingClient;
                            if (!$client->user) {
                                throw new \Exception('Client existant sans compte utilisateur associé');
                            }
                        } else {
                            // Créer un nouveau client et user
                            $isNewClient = true;

                            // Générer mot de passe temporaire et code SMS
                            $password = Str::random(10);
                            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                            // Créer un nouveau client
                            $client = Client::create([
                                'nom' => explode(' ', $data['client']['titulaire'])[0] ?? $data['client']['titulaire'],
                                'prenom' => explode(' ', $data['client']['titulaire'])[1] ?? '',
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
                        }
                    }
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

                // Créer les données de notification seulement pour les nouveaux clients
                if ($isNewClient && $password && $code) {
                    $clientNotificationData = ClientNotificationData::fromClientAndFormData(
                        $client,
                        $data['client'],
                        $password,
                        $code,
                        $compte->numero_compte
                    );

                    // Envoyer les notifications
                    event(new AccountCreated($clientNotificationData));
                }
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
     *     summary="Afficher les détails d'un compte (y compris les comptes épargne archivés)",
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
     *         description="Détails du compte récupérés. Pour les comptes épargne, les informations de blocage sont affichées même si le compte n'est pas actuellement bloqué.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé (ni dans la base principale, ni dans les archives)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur serveur inattendue")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            // Validation UUID
            if ($error = $this->validateUuidOrRespond($id, 'compte')) {
                return $error;
            }

            // Recherche du compte (lance une exception si non trouvé)
            $compte = $this->compteService->findCompteById($id);

            return $this->successResponse(
                new CompteResource($compte->load('client')),
                'Détails du compte récupérés'
            );
        } catch (CompteNotFoundException $e) {
            return $this->structuredErrorResponse(
                $e->getErrorCode(),
                $e->getMessage(),
                $e->getErrorDetails(),
                $e->getHttpStatusCode()
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du compte', [
                'compte_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->structuredErrorResponse(
                'INTERNAL_ERROR',
                'Une erreur interne est survenue lors de la récupération du compte',
                ['compteId' => $id],
                500
            );
        }
    }

    /**
     * Afficher un compte spécifique par numéro
     * @OA\Get(
     *     path="/api/v1/comptes/numero/{numero}",
     *     summary="Afficher les détails d'un compte par numéro",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         required=true,
     *         description="Numéro du compte",
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
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec le numéro spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur serveur")
     *         )
     *     )
     * )
     */
    public function showByNumero(string $numero)
    {
        try {
            Log::info('🔍 Recherche de compte par numéro', ['numero' => $numero]);

            // Recherche du compte (lance une exception si non trouvé)
            $compte = $this->compteService->findCompteByNumero($numero);

            return $this->successResponse(
                new CompteResource($compte->load('client')),
                'Détails du compte récupérés'
            );
        } catch (CompteNotFoundException $e) {
            return $this->structuredErrorResponse(
                $e->getErrorCode(),
                $e->getMessage(),
                $e->getErrorDetails(),
                $e->getHttpStatusCode()
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du compte par numéro', [
                'numero' => $numero,
                'error' => $e->getMessage()
            ]);

            return $this->structuredErrorResponse(
                'INTERNAL_ERROR',
                'Une erreur interne est survenue lors de la récupération du compte',
                ['numero' => $numero],
                500
            );
        }
    }

    /**
     * PUT /api/v1/comptes/{compte}
     * @OA\Put(
     *     path="/api/v1/comptes/{compte}",
     *     summary="Mettre à jour un compte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         required=true,
     *         description="ID du compte à mettre à jour",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", example="Jane Doe"),
     *             @OA\Property(property="informationsClient", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="nci", type="string", example="123456789012"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane.doe@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="newpassword123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données de requête invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur côté serveur")
     *         )
     *     )
     * )
     */
    public function update(UpdateCompteRequest $request, string $id)
    {
        try {
            // Validation UUID
            if ($error = $this->validateUuidOrRespond($id, 'compte')) {
                return $error;
            }

            // Recherche et validation du compte
            $compte = $this->compteService->findCompteById($id);
            $this->compteService->ensureCompteIsModifiable($compte);

            Log::info('📝 Mise à jour du compte', [
                'compte_id' => $compte->id,
                'data' => $request->all(),
            ]);

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

    /**
     * DELETE /api/v1/comptes/{compte}
     * @OA\Delete(
     *     path="/api/v1/comptes/{compte}",
     *     summary="Supprimer un compte (soft delete)",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         description="ID du compte à supprimer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="statut", type="string", example="ferme"),
     *                 @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur côté serveur")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            // Validation UUID
            if ($error = $this->validateUuidOrRespond($id, 'compte')) {
                return $error;
            }

            // Recherche et validation du compte
            $compte = $this->compteService->findCompteById($id);
            $this->compteService->ensureCompteIsModifiable($compte);

            Log::info('🗑️ Suppression du compte', [
                'compte_id' => $compte->id,
            ]);

            // Vérifier si le compte n'est pas déjà fermé
            if ($compte->statut === 'ferme') {
                return $this->structuredErrorResponse(
                    'COMPTE_DEJA_FERME',
                    'Ce compte est déjà fermé',
                    ['compteId' => $id, 'statut' => $compte->statut],
                    400
                );
            }

            // Mettre à jour le statut et la date de fermeture
            $compte->update([
                'statut' => 'ferme',
                'date_fermeture' => now(),
            ]);

            // Soft delete du compte
            $compte->delete();

            return $this->successResponse([
                'id' => $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'statut' => $compte->statut,
                'dateFermeture' => $compte->date_fermeture?->toISOString(),
            ], 'Compte supprimé avec succès');
        } catch (CompteNotFoundException $e) {
            return $this->structuredErrorResponse(
                $e->getErrorCode(),
                $e->getMessage(),
                $e->getErrorDetails(),
                $e->getHttpStatusCode()
            );
        } catch (CompteArchivedException $e) {
            return $this->structuredErrorResponse(
                $e->getErrorCode(),
                $e->getMessage(),
                $e->getErrorDetails(),
                $e->getHttpStatusCode()
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du compte', [
                'compte_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->structuredErrorResponse(
                'INTERNAL_ERROR',
                'Une erreur interne est survenue lors de la suppression du compte',
                ['compteId' => $id],
                500
            );
        }
    }

    /**
     * POST /api/v1/comptes/{compte}/bloquer
     * @OA\Post(
     *     path="/api/v1/comptes/{compte}/bloquer",
     *     summary="Bloquer un compte épargne (immédiat ou programmé)",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         description="ID du compte à bloquer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dateBlocage", "motif", "duree", "unite"},
     *             @OA\Property(property="dateBlocage", type="string", format="date-time", description="Date et heure de début du blocage (ISO 8601)", example="2025-10-29T10:00:00Z"),
     *             @OA\Property(property="motif", type="string", description="Motif du blocage", example="Activité suspecte détectée"),
     *             @OA\Property(property="duree", type="integer", description="Durée du blocage", example=30, minimum=1),
     *             @OA\Property(property="unite", type="string", enum={"minute", "minutes", "jours", "semaines", "mois", "annees"}, description="Unité de temps pour la durée", example="minutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Demande de blocage traitée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Blocage programmé pour le 2025-10-29T10:00:00Z"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="statut", type="string", example="actif", description="Peut être 'actif' si blocage programmé ou 'bloque' si immédiat"),
     *                 @OA\Property(property="motifBlocage", type="string", example="Activité suspecte détectée"),
     *                 @OA\Property(property="dateBlocage", type="string", format="date-time", example="2025-10-29T10:00:00Z"),
     *                 @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time", example="2025-11-28T10:00:00Z"),
     *                 @OA\Property(property="scheduled", type="boolean", example=true, description="true si blocage programmé, false si immédiat")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide ou compte non éligible au blocage",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Seuls les comptes épargne actifs peuvent être bloqués")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur côté serveur")
     *         )
     *     )
     * )
     */
    public function bloquer(BloquerCompteRequest $request, string $id)
    {
        try {
            // Validation UUID
            if ($error = $this->validateUuidOrRespond($id, 'compte')) {
                return $error;
            }

            // Recherche et validation du compte
            $compte = $this->compteService->findCompteById($id);
            $this->compteService->ensureCompteIsModifiable($compte);

            Log::info('🔒 Blocage du compte', [
                'compte_id' => $compte->id,
                'data' => $request->all(),
            ]);

            // Vérifier que le compte est de type épargne
            if ($compte->type !== 'epargne') {
                return $this->structuredErrorResponse(
                    'COMPTE_TYPE_INVALID',
                    'Seuls les comptes épargne peuvent être bloqués',
                    ['compteId' => $id, 'type' => $compte->type],
                    400
                );
            }

            // Vérifier que le compte est actif
            if ($compte->statut !== 'actif') {
                return $this->structuredErrorResponse(
                    'COMPTE_STATUT_INVALID',
                    'Seuls les comptes actifs peuvent être bloqués',
                    ['compteId' => $id, 'statut' => $compte->statut],
                    400
                );
            }

            $data = $request->validated();

            // Parse provided dateBlocage (user can schedule a future block)
            $dateBlocage = Carbon::parse($data['dateBlocage']);

            // Calculer la date de déblocage prévue à partir de la date de début
            $dateDeblocagePrevue = $dateBlocage->copy();
            // accept both 'minute' and 'minutes'
            if (in_array($data['unite'], ['minute', 'minutes'], true)) {
                $dateDeblocagePrevue->addMinutes($data['duree']);
            } elseif ($data['unite'] === 'jours') {
                $dateDeblocagePrevue->addDays($data['duree']);
            } elseif ($data['unite'] === 'semaines') {
                $dateDeblocagePrevue->addWeeks($data['duree']);
            } elseif ($data['unite'] === 'mois') {
                $dateDeblocagePrevue->addMonths($data['duree']);
            } else {
                $dateDeblocagePrevue->addYears($data['duree']);
            }

            // Enregistrer les informations de blocage (métadonnées + champs dédiés)
            $metas = $compte->metadonnees ?? [];
            $metas['blocage'] = [
                'duree' => $data['duree'],
                'unite' => $data['unite'],
            ];

            // Préparer les données à mettre à jour
            $update = [
                'motifBlocage' => $data['motif'],
                'dateBlocage' => $dateBlocage,
                'dateDeblocagePrevue' => $dateDeblocagePrevue,
                'metadonnees' => $metas,
            ];

            // Ne changer le statut que si la date de début de blocage est déjà échue
            if ($dateBlocage->lte(now())) {
                $update['statut'] = 'bloque';
            }

            $compte->update($update);

            return $this->successResponse([
                'id' => $compte->id,
                'statut' => $compte->statut,
                'motifBlocage' => $compte->motifBlocage,
                'dateBlocage' => $compte->dateBlocage?->toISOString(),
                'dateDeblocagePrevue' => $compte->dateDeblocagePrevue?->toISOString(),
                'scheduled' => $dateBlocage->gt(now()),
            ], 'Informations de blocage enregistrées');
        } catch (CompteNotFoundException $e) {
            return $this->structuredErrorResponse(
                $e->getErrorCode(),
                $e->getMessage(),
                $e->getErrorDetails(),
                $e->getHttpStatusCode()
            );
        } catch (CompteArchivedException $e) {
            return $this->structuredErrorResponse(
                $e->getErrorCode(),
                $e->getMessage(),
                $e->getErrorDetails(),
                $e->getHttpStatusCode()
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors du blocage du compte', [
                'compte_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->structuredErrorResponse(
                'INTERNAL_ERROR',
                'Une erreur interne est survenue lors du blocage du compte',
                ['compteId' => $id],
                500
            );
        }
    }
}
