<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Adresse email valide existant dans la base de données"),
 *     @OA\Property(property="password", type="string", format="password", example="password123", minLength=8, description="Mot de passe d'au moins 8 caractères"),
 *     @OA\Property(property="code", type="string", example="000000", description="Code de vérification (requis uniquement pour les comptes inactifs)")
 * )
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Connexion réussie"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *         @OA\Property(property="token_type", type="string", example="Bearer"),
 *         @OA\Property(property="expires_in", type="integer", example=1296000),
 *         @OA\Property(property="scopes", type="array", @OA\Items(type="string"), example={"read-comptes", "create-comptes"})
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
     *     description="Authentifie un utilisateur. Pour les comptes actifs, seuls email/password sont requis. Pour les comptes inactifs, un code de vérification est nécessaire pour l'activation.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse"),
     *         @OA\Header(
     *             header="Set-Cookie",
     *             description="Cookies HTTP-only contenant access_token et refresh_token",
     *             @OA\Schema(type="string", example="access_token=eyJ0...; HttpOnly; Secure; SameSite=Strict")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides ou code de vérification incorrect",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Identifiants invalides"),
     *             @OA\Property(property="error", type="string", example="INVALID_CREDENTIALS")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Code de vérification requis pour compte inactif",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code de vérification requis pour activer votre compte"),
     *             @OA\Property(property="error", type="string", example="VERIFICATION_CODE_REQUIRED")
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

        // 🎯 LOGIQUE DIFFÉRENCIÉE : Vérifier si le compte nécessite une activation
        if (!$user->is_active) {
            // 🔐 COMPTE INACTIF : Nécessite un code d'activation

            // Vérifier que le code est fourni
            if (empty($request->code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de vérification requis pour activer votre compte',
                    'error' => 'VERIFICATION_CODE_REQUIRED'
                ], 422);
            }

            // Vérifier que l'utilisateur a un code de vérification configuré
            if (empty($user->verification_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun code de vérification configuré pour ce compte',
                    'error' => 'NO_VERIFICATION_CODE_SET'
                ], 422);
            }

            // Vérifier que le code fourni correspond
            if ($user->verification_code !== $request->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de vérification invalide',
                    'error' => 'INVALID_VERIFICATION_CODE'
                ], 401);
            }

            // Vérifier que le code n'est pas expiré
            if ($user->code_expires_at && now()->greaterThan($user->code_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de vérification expiré. Veuillez demander un nouveau code.',
                    'error' => 'VERIFICATION_CODE_EXPIRED'
                ], 401);
            }

            // 🎉 Code valide : Activer le compte
            $user->update([
                'is_active' => true,
                'verification_code' => null, // Nettoyer le code après activation
                'code_expires_at' => null,
            ]);
        }

        // ✅ Si le compte est déjà actif, on continue directement vers la génération des tokens

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
                'access_token' => $token,
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
     *     description="Rafraîchit le token d'accès en utilisant le refresh token stocké dans les cookies HTTP-only",
     *     tags={"Authentification"},
     *     security={{"cookieAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/AuthResponse"),
     *         @OA\Header(
     *             header="Set-Cookie",
     *             description="Nouveaux cookies HTTP-only avec les tokens rafraîchis",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Refresh token manquant ou invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Refresh token manquant")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        // Lire le refresh token depuis le cookie HTTP-only
        $refreshToken = $request->cookie('refresh_token');

        if (empty($refreshToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token manquant dans les cookies',
                'error' => 'REFRESH_TOKEN_MISSING'
            ], 401);
        }

        // Rechercher le token dans la base de données
        $tokenModel = \Laravel\Passport\Token::where('id', $this->getTokenId($refreshToken))
            ->where('revoked', false)
            ->first();

        if (!$tokenModel) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token invalide ou révoqué',
                'error' => 'INVALID_REFRESH_TOKEN'
            ], 401);
        }

        // Vérifier si le token est expiré
        if ($tokenModel->expires_at && now()->greaterThan($tokenModel->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token expiré',
                'error' => 'REFRESH_TOKEN_EXPIRED'
            ], 401);
        }

        // Récupérer l'utilisateur associé au token
        $user = User::find($tokenModel->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
                'error' => 'USER_NOT_FOUND'
            ], 401);
        }

        // Révoquer l'ancien refresh token
        $tokenModel->revoke();

        // Générer de nouveaux tokens
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
                'access_token' => $newToken,
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

    /**
     * Extraire l'ID du token depuis le JWT
     */
    private function getTokenId($token)
    {
        try {
            // Décoder le JWT (partie payload)
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($tokenParts[1]), true);
            return $payload['jti'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
