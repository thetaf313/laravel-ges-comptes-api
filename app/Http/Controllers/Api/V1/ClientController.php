<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Traits\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="ClientResource",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="uuid"),
 *     @OA\Property(property="nom", type="string", example="Doe"),
 *     @OA\Property(property="prenom", type="string", example="John"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="telephone", type="string", example="+221771234567"),
 *     @OA\Property(property="cni", type="string", example="123456789012"),
 *     @OA\Property(property="adresse", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ClientController extends Controller
{
    use RestResponse;

    /**
     * Afficher les détails d'un client par son ID
     * @OA\Get(
     *     path="/api/v1/clients/{client}",
     *     summary="Afficher les détails d'un client par son ID",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="ID du client (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du client récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du client récupérés"),
     *             @OA\Property(property="data", ref="#/components/schemas/ClientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="CLIENT_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le client avec l'ID spécifié n'existe pas"),
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
    public function show(Client $client)
    {
        try {
            Log::info('🔍 Affichage des détails du client', ['client_id' => $client->id]);

            // Charger la relation user si nécessaire
            $client->load('user');

            return $this->successResponse(
                new ClientResource($client),
                'Détails du client récupérés'
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du client', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);

            return $this->structuredErrorResponse(
                'INTERNAL_ERROR',
                'Une erreur interne est survenue lors de la récupération du client',
                ['clientId' => $client->id],
                500
            );
        }
    }

    /**
     * Rechercher un client par téléphone ou NCI
     * @OA\Get(
     *     path="/api/v1/clients/{identifier}",
     *     summary="Rechercher un client par téléphone ou NCI",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         required=true,
     *         description="Numéro de téléphone ou numéro de carte d'identité (NCI)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client trouvé"),
     *             @OA\Property(property="data", ref="#/components/schemas/ClientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="CLIENT_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Aucun client trouvé avec ce numéro de téléphone ou NCI"),
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
    public function searchByIdentifier(string $identifier)
    {
        try {
            Log::info('🔍 Recherche de client', ['identifier' => $identifier]);

            // Recherche par téléphone d'abord
            $client = Client::where('telephone', $identifier)->first();

            // Si pas trouvé par téléphone, recherche par NCI
            if (!$client) {
                $client = Client::where('cni', $identifier)->first();
            }

            // Si toujours pas trouvé, retourner une erreur
            if (!$client) {
                return $this->structuredErrorResponse(
                    'CLIENT_NOT_FOUND',
                    'Aucun client trouvé avec ce numéro de téléphone ou NCI',
                    ['identifier' => $identifier],
                    404
                );
            }

            // Charger les relations nécessaires
            $client->load('user');

            return $this->successResponse(
                new ClientResource($client),
                'Client trouvé'
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche du client', [
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ]);

            return $this->structuredErrorResponse(
                'INTERNAL_ERROR',
                'Une erreur interne est survenue lors de la recherche du client',
                ['identifier' => $identifier],
                500
            );
        }
    }

    /**
     * Lister les comptes d'un client
     * @OA\Get(
     *     path="/api/v1/clients/{client}/comptes",
     *     summary="Lister les comptes d'un client",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comptes du client récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function comptesByClient(Client $client)
    {
        $comptes = $client->comptes()->with('client')->get();

        return $this->successResponse(
            CompteResource::collection($comptes),
            'Comptes du client récupérés'
        );
    }
}
