<?php

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

// Route::middleware([LoggingMiddleware::class])->group(function () {
//     Route::prefix('v1')->group(function () {
//         Route::get('/comptes', [CompteController::class, 'index']);
//         Route::post('/comptes', [CompteController::class, 'store'])->name('comptes.store');
//         Route::get('/comptes/{compte}', [CompteController::class, 'show']);
//         Route::get('clients/{client}/comptes', [ClientController::class, 'comptesByClient']);
//     });
// });

Route::prefix('v1')->group(function () {
    Route::get('/comptes', [CompteController::class, 'index']);
    Route::post('/comptes', [CompteController::class, 'store']);
    Route::get('/comptes/{compte}', [CompteController::class, 'show']);
    Route::patch('/comptes/{compte}', [CompteController::class, 'update']);
    Route::get('clients/{client}/comptes', [ClientController::class, 'comptesByClient']);
});

Route::get('/v1/test', fn() => response()->json(['ok' => true]));
