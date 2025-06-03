<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdminWorkOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->middleware('auth:api')->group(function () {
    Route::get('/orders', [AdminWorkOrderController::class, 'apiViewOrders']);
    Route::get('/orders/{id}', [AdminWorkOrderController::class, 'apiViewOrder']);
    Route::post('/orders/{id}/status', [AdminWorkOrderController::class, 'apiUpdateStatus']);
    Route::delete('/orders/{id}', [AdminWorkOrderController::class, 'apiDeleteOrder']);
    Route::get('/orders/{id}/video', [AdminWorkOrderController::class, 'apiViewVideo']);
    Route::get('/orders/{id}/logo', [AdminWorkOrderController::class, 'apiViewLogo']);
});
