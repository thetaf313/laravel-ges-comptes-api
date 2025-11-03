<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="2.7.0",
 *      title="API Gestion des Comptes",
 *      description="API pour la gestion des comptes avec authentification OAuth2",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Serveur de développement local"
 * )
 *
 * @OA\Server(
 *      url="https://sayande-moustapha-gestion-comptes-api.onrender.com",
 *      description="Serveur de production (Render)"
 * )
 *
 * Note: Pour ajouter un serveur staging ou autre environnement, ajoutez :
 * @OA\Server(
 *      url="https://staging.example.com",
 *      description="Serveur de staging"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Utilisez le token d'accès obtenu via /api/v1/auth/login"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="cookieAuth",
 *      type="apiKey",
 *      in="cookie",
 *      name="access_token",
 *      description="Authentication via HTTP-only cookie (pour les navigateurs)"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
