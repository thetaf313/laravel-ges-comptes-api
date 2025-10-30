<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Adresse email valide existant dans la base de données"),
 *     @OA\Property(property="password", type="string", format="password", example="password123", minLength=8, description="Mot de passe d'au moins 8 caractères")
 * )
 * @OA\Schema(
 *     schema="RefreshTokenRequest",
 *     type="object",
 *     required={"refresh_token"},
 *     @OA\Property(property="refresh_token", type="string", example="def50200...", description="Token de rafraîchissement valide")
 * )
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Connexion réussie"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="user", ref="#/components/schemas/User"),
 *         @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *         @OA\Property(property="refresh_token", type="string", example="def50200..."),
 *         @OA\Property(property="token_type", type="string", example="Bearer"),
 *         @OA\Property(property="expires_in", type="integer", example=1296000)
 *     )
 * )
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Connexion utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Identifiants invalides")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        // Vérifier existence + mot de passe
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides',
                'error' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // Vérifier que l'utilisateur est actif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Compte utilisateur inactif',
                'error' => 'USER_INACTIVE'
            ], 403);
        }

        // Vérifier le code de vérification et sa validité
        if (empty($user->verification_code) || $user->verification_code !== $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification invalide',
                'error' => 'INVALID_VERIFICATION_CODE'
            ], 401);
        }

        if ($user->code_expires_at && now()->greaterThan($user->code_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification expiré',
                'error' => 'VERIFICATION_CODE_EXPIRED'
            ], 401);
        }

        // Créer le token d'accès avec les scopes appropriés
        $scopes = $user->getScopes();
        $tokenResult = $user->createToken('API Access Token', $scopes);
        $token = $tokenResult->accessToken;

        // Créer le refresh token
        $refreshTokenResult = $user->createToken('API Refresh Token');
        $refreshToken = $refreshTokenResult->accessToken;

        // Stocker les tokens dans des cookies sécurisés (httpOnly)
        $accessCookie = Cookie::make(
            'access_token',
            $token,
            15 * 24 * 60, // 15 days in minutes
            '/',
            null,
            true, // secure
            true, // httpOnly
            false,
            'Strict'
        );

        $refreshCookie = Cookie::make(
            'refresh_token',
            $refreshToken,
            30 * 24 * 60, // 30 days
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'token_type' => 'Bearer',
                'expires_in' => 15 * 24 * 60 * 60,
                'scopes' => $scopes,
            ],
        ])->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Rafraîchir le token d'accès",
     *     tags={"Authentification"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="refresh_token", type="string", example="def50200...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token de rafraîchissement invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function refresh(RefreshTokenRequest $request)
    {
        // Lire le refresh token depuis le cookie
        $refreshToken = $request->cookie('refresh_token');

        if (empty($refreshToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token manquant',
            ], 401);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        // Ici on ne valide pas la correspondance exacte du refresh token pour simplifier,
        // en production il faudrait vérifier la présence du refresh token dans la table tokens.
        $scopes = $user->getScopes();
        $newTokenResult = $user->createToken('API Access Token', $scopes);
        $newToken = $newTokenResult->accessToken;
        $newRefreshTokenResult = $user->createToken('API Refresh Token');
        $newRefreshToken = $newRefreshTokenResult->accessToken;

        $accessCookie = Cookie::make('access_token', $newToken, 15 * 24 * 60, '/', null, true, true, false, 'Strict');
        $refreshCookie = Cookie::make('refresh_token', $newRefreshToken, 30 * 24 * 60, '/', null, true, true, false, 'Strict');

        return response()->json([
            'success' => true,
            'message' => 'Token rafraîchi avec succès',
            'data' => [
                'token_type' => 'Bearer',
                'expires_in' => 15 * 24 * 60 * 60,
                'scopes' => $scopes,
            ],
        ])->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Déconnexion utilisateur",
     *     tags={"Authentification"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        // Révoquer tous les tokens de l'utilisateur
        $user->tokens->each(function ($token) {
            $token->revoke();
        });

        // Supprimer les cookies d'auth
        $forgetAccess = Cookie::forget('access_token');
        $forgetRefresh = Cookie::forget('refresh_token');

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ])->withCookie($forgetAccess)->withCookie($forgetRefresh);
    }
}
