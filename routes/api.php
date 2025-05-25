<?php

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

// Route::apiResource('/presensi', App\Http\Controllers\Api\PresensiController::class);
Route::apiResource('/presensi', App\Http\Controllers\Api\PresensiController::class)->names([
    'index' => 'api.presensi.index',
    'store' => 'api.presensi.store',
    'show' => 'api.presensi.show',
    'update' => 'api.presensi.update',
    'destroy' => 'api.presensi.destroy',
]);
Route::post('/presensi/log', [App\Http\Controllers\Api\PresensiController::class, 'log']);
