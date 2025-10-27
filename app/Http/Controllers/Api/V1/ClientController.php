<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Traits\RestResponse;

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
