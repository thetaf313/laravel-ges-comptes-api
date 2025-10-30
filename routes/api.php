<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Middleware\LoggingMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes d'authentification OAuth2
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

// Routes API protégées
// Routes API protégées - SÉCURITÉ TEMPORAIREMENT DÉSACTIVÉE POUR TESTS
// Route::prefix('v1')->middleware(['auth:api', 'role:read-comptes'])->group(function () {
Route::prefix('v1')->group(function () {
    Route::get('/comptes', [CompteController::class, 'index']);
    Route::post('/comptes', [CompteController::class, 'store']); // ->middleware('role:create-comptes');
    Route::get('/comptes/{id}', [CompteController::class, 'show']);
    Route::get('/comptes/numero/{numero}', [CompteController::class, 'showByNumero']);
    Route::patch('/comptes/{id}', [CompteController::class, 'update']); // ->middleware('role:update-comptes');
    Route::delete('/comptes/{id}', [CompteController::class, 'destroy']); // ->middleware('role:delete-comptes');
    Route::post('/comptes/{id}/bloquer', [CompteController::class, 'bloquer']); // ->middleware('role:block-comptes');
    Route::get('clients/{client}', [ClientController::class, 'show'])->where('client', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    Route::get('clients/{client}/comptes', [ClientController::class, 'comptesByClient']);
    Route::get('clients/search/{identifier}', [ClientController::class, 'searchByIdentifier']);
});

Route::get('/v1/test', fn() => response()->json(['ok' => true]));
