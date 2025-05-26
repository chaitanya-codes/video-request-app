<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\VideoRequestController;
use App\Http\Controllers\WorkOrderController;
use App\Models\WorkOrder;
use App\Http\Middleware\Authenticate;

Route::get('/', function () {
    return "<div style='font-size: 60px'><a href='/video-request/create'><b>Fill Form</b></a><br>
            <br><a href='/admin/orders'><b>Admin => View Orders</b></a>
            <br><a href='/orders/3'><b>User => View Order</b></a></div>";
});
Route::get('/video-request/create', [VideoRequestController::class, 'form'])->name("video-requests.create");

Route::post('/video-request', [VideoRequestController::class, 'submitForm'])->name("video-requests.store");

Route::post('/video-request/place-order', [VideoRequestController::class, 'placeOrder'])->name("video-requests.place-order");

Route::get('/orders/{id}', [VideoRequestController::class, 'viewOrder'])->name("order.view");
Route::get('/orders/{id}/file', [VideoRequestController::class, 'viewOrderFile'])->name("order.view-file");
Route::post('/orders/{id}/update-status', [VideoRequestController::class, 'reviewOrder'])->name("order.update-status");

Route::middleware([Authenticate::class])->group(function() {
    Route::get('/admin/orders', [WorkOrderController::class, 'viewOrders'])->name("admin.orders.index");

    Route::delete('/admin/orders/{id}', [WorkOrderController::class, 'deleteOrder'])->name("admin.orders.delete");
    Route::get('/admin/orders/{id}', [WorkOrderController::class, 'viewOrder'])->name("admin.orders.view");
    Route::post('/admin/orders/{id}/status', [WorkOrderController::class, 'updateStatus'])->name("admin.orders.update-status");

    Route::get('/admin/orders/{id}/video', [WorkOrderController::class, 'viewVideo'])->name("admin.orders.view-video");
    Route::get('/admin/orders/{id}/logo', [WorkOrderController::class, 'viewLogo'])->name("admin.orders.view-logo");
});