<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\Client\ClCounterpartyController;
use App\Http\Controllers\Api\V1\Client\ClOrderController;
use App\Http\Controllers\Api\V1\Client\ClStoreController;
use App\Http\Controllers\Api\V1\CounterpartyController;
use App\Http\Controllers\Api\V1\Courier\TtnController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\InfoController;
use App\Http\Controllers\Api\V1\OneC\Category1CController;
use App\Http\Controllers\Api\V1\OneC\Counterparty1CController;
use App\Http\Controllers\Api\V1\OneC\Order1CController;
use App\Http\Controllers\Api\V1\OneC\Producer1CController;
use App\Http\Controllers\Api\V1\OneC\Product1CController;
use App\Http\Controllers\Api\V1\OneC\ProductPrice1CController;
use App\Http\Controllers\Api\V1\OneC\ProductStock1CController;
use App\Http\Controllers\Api\V1\OneC\Ttn1CController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Проверка авторизации
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Открытые маршруты (регистрация, вход)
Route::prefix('v1/auth')
    ->middleware(['throttle:api'])
    ->group(function () {
        Route::post('request-code', [AuthController::class, 'requestCode']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('verify-code', [AuthController::class, 'verifyCode']);
    });

Route::prefix('v1')
    ->middleware(['throttle:api'])
    ->group(function () {

        Route::get('/faq', [InfoController::class, 'faqs']);
        Route::get('/about', [InfoController::class, 'about']);
        Route::get('/banners', [InfoController::class, 'banners']);

    });

// Защищённые маршруты (только авторизованные пользователи)
Route::middleware(['throttle:api', 'auth:sanctum'])
    ->prefix('v1')
    ->group(function () {
        Route::get('logout', [AuthController::class, 'logout']);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::patch('profile/update', [ProfileController::class, 'update']);
        Route::delete('profile', [ProfileController::class, 'destroy']);

        //Избранные
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/toggle/{productId}', [FavoriteController::class, 'toggle']);


        //Роуты 1С старые
        Route::post('counterparties/bulk', [CounterpartyController::class, 'bulkStore']);
        Route::post('categories/bulk', [CategoryController::class, 'bulkStore']);
        Route::post('products/bulk', [ProductController::class, 'bulkStore']);


        //каталог
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::patch('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('product/{product_id}', [ProductController::class, 'show'])->name('products.show');

        //Роуты торгового
        Route::middleware(['role:2'])
            ->group(function () {
                //Контрагенты
                Route::get('counterparties', [CounterpartyController::class, 'index']);
                Route::get('counterparty/{counterparty}', [CounterpartyController::class, 'show']);
                //Магазин с адресом
                Route::apiResource('stores', StoreController::class);
                Route::get('stores/bycounterparty/{counterparty_id}', [StoreController::class, 'storeByCounterparty']);
                //Заказы
                Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
                Route::get('orders/{id}', [OrderController::class, 'show']);
                Route::post('orders/create', [OrderController::class, 'createOrder']);
                Route::patch('orders/cancel/{id}', [OrderController::class, 'cancel']);
                Route::patch('orders/{id}', [OrderController::class, 'update']);  // смена статуса
                Route::delete('orders/{id}', [OrderController::class, 'destroy']); // отмена/удаление
            });
        //Роуты клиента
        Route::prefix('client')
            ->middleware(['role:4'])
            ->group(function () {
                Route::get('counterparty', [ClCounterpartyController::class, 'show']);

                Route::get('orders', [ClOrderController::class, 'index'])->name('client.orders.index');
                Route::get('orders/{id}', [ClOrderController::class, 'show']);
                Route::post('orders/create', [ClOrderController::class, 'createOrder']);
                Route::patch('orders/cancel/{id}', [ClOrderController::class, 'cancel']);
                Route::patch('orders/{id}', [ClOrderController::class, 'update']);  // смена статуса
                Route::delete('orders/{id}', [ClOrderController::class, 'destroy']); // отмена/удаление

                Route::get('stores', [ClStoreController::class, 'index']);
                Route::get('stores/{id}', [ClStoreController::class, 'show']);
                Route::post('stores', [ClStoreController::class, 'store']);
                Route::patch('stores/{id}', [ClStoreController::class, 'update']);
                Route::delete('stores/{id}', [ClStoreController::class, 'destroy']);
            });

        //Роуты курьера
        Route::prefix('courier')
            ->group(function () {
                Route::get('ttns', [TtnController::class, 'index']);
                Route::get('ttns/{id}', [TtnController::class, 'show']);
                Route::post('ttns/reorder', [TtnController::class, 'reorderItems']);
                Route::get('orders/{id}', [\App\Http\Controllers\Api\V1\Courier\OrderController::class, 'show']);
                Route::post('orders/update/{id}', [\App\Http\Controllers\Api\V1\Courier\OrderController::class, 'updateOrder']);
                Route::post('orders/update-status/{id}', [\App\Http\Controllers\Api\V1\Courier\OrderController::class, 'updateStatus']);
                Route::get('reports', [\App\Http\Controllers\Api\V1\Courier\OrderController::class, 'courierDailyReport']);
                Route::get('debtors', [\App\Http\Controllers\Api\V1\Courier\CourierDebtorsController::class, 'index']);
                Route::post('orders/{id}/repay', [\App\Http\Controllers\Api\V1\Courier\CourierDebtorsController::class, 'payDebt']);

                Route::post('orders/pay-order/{id}', [\App\Http\Controllers\Api\V1\Courier\OrderPaymentController::class, 'createPayment']);
                Route::post('orders/confirm-debt/{id}', [\App\Http\Controllers\Api\V1\Courier\OrderPaymentController::class, 'verifyDebtCode']);
            });

        //api для 1С
        Route::prefix('one-c')
            ->group(function () {
                Route::get('orders', [Order1CController::class, 'index']);
                Route::get('orders/{id}', [Order1CController::class, 'show']);
                Route::post('orders/update/{id}', [Order1CController::class, 'updateStatus']);

                Route::post('categories/bulk', [Category1CController::class, 'bulkStore']);
                Route::post('producers/bulk', [Producer1CController::class, 'bulkStore']);
                Route::post('counterparties/bulk', [Counterparty1CController::class, 'bulkStore']);
                Route::post('products/bulk', [Product1CController::class, 'bulkStore']);
                Route::post('stocks/bulk', [ProductStock1CController::class, 'bulkStore']);
                Route::post('prices/bulk', [ProductPrice1CController::class, 'bulkStore']);

                Route::post('ttns/bulk', [Ttn1CController::class, 'bulkStore']);
            });
    });


// Загрузка медиа для товара   для теста
Route::prefix('products')->group(function () {
    Route::post('/{productId}/media/{phone}', [ProductImageController::class, 'store']);
    Route::patch('/{productId}/media/{mediaId}/set-main', [ProductImageController::class, 'setMain']);
    Route::patch('/{productId}/media/reorder', [ProductImageController::class, 'reorder']);
    Route::delete('/{productId}/media/{mediaId}', [ProductImageController::class, 'destroy']);
});
