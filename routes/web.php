<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\FormController;
use App\Http\Controllers\VideoRequestController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('home');
})->name("home");
Route::get('/video-request/create', [VideoRequestController::class, 'form'])->name("video-requests.create");

Route::post('/video-request', [VideoRequestController::class, 'submitForm'])->name("video-requests.store");

Route::post('/video-request/place-order', [VideoRequestController::class, 'placeOrder'])->name("video-requests.place-order");

// Route::get('/login', function () {
//     return "<div style='font-size: 60px'>Login Page</div>";
// })->name("login");
Route::get('/orders', [VideoRequestController::class, 'viewOrders'])->name("order.index");
Route::get('/orders/{id}', [VideoRequestController::class, 'viewOrder'])->name("order.view");
Route::get('/orders/{id}/file', [VideoRequestController::class, 'viewOrderFile'])->name("order.view-file");
Route::post('/orders/{id}/update-status', [VideoRequestController::class, 'reviewOrder'])->name("order.update-status");

Route::middleware([Authenticate::class])->group(function() {
    Route::get('/admin', [WorkOrderController::class, 'dashboard'])->name("admin.dashboard");
    Route::get('/admin/orders', [WorkOrderController::class, 'viewOrders'])->name("admin.orders.index");
    Route::get('/admin/users', [WorkOrderController::class, 'viewUsers'])->name("admin.users.index");

    Route::delete('/admin/orders/{id}', [WorkOrderController::class, 'deleteOrder'])->name("admin.orders.delete");
    Route::get('/admin/orders/{id}', [WorkOrderController::class, 'viewOrder'])->name("admin.orders.view");
    Route::post('/admin/orders/{id}/status', [WorkOrderController::class, 'updateStatus'])->name("admin.orders.update-status");

    Route::get('/admin/orders/{id}/video', [WorkOrderController::class, 'viewVideo'])->name("admin.orders.view-video");
    Route::get('/admin/orders/{id}/logo', [WorkOrderController::class, 'viewLogo'])->name("admin.orders.view-logo");
    Route::get('/admin/orders/{id}/file', [WorkOrderController::class, 'viewFile'])->name("admin.orders.view-file");

    Route::get('/admin/notifications/stream', function () {
        return response()->stream(function () {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);
            ob_implicit_flush(true);

            echo "retry: 2000\n";
            $order = Cache::pull('latest_order_for_admin');
            if ($order) {
                echo "id: {$order['id']}\n";
                echo "event: order\n";
                echo "data: " . json_encode($order) . "\n\n";
            } else {
                echo ": keep-alive\n\n";
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    });
});