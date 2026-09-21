<?php

use App\Http\Controllers\admin\AttributeValueController;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BannerController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\CouponController;
use App\Http\Controllers\admin\DashBoardController;
use App\Http\Controllers\admin\NotificationController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\ProductsController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\TicketController;
use App\Http\Controllers\admin\WalletBonusController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/clear-all-cache', function () {
    Artisan::call('optimize:clear');
    return response()->json([
        'status'  => 'success',
        'message' => 'All cache cleared successfully!'
    ]);
});

Route::prefix('admin')->group(function () {

    Route::middleware(['guest'])->as('admin.')->group(function () {
        Route::view('/login', 'auth.login')->name('login');
        Route::view('/register', 'auth.register')->name('register');
        Route::controller(AuthController::class)->group(function () {
            Route::post('/authenticate', 'adminAuthenticate')->name('authenticate');
            Route::post('/register/update', 'registerUpdate')->name('register.update');
        });
    });

    Route::middleware('auth:admin')->group(function () {

        Route::get('/dashboard', [DashBoardController::class, 'index'])->name('dashboard');
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/user_logout', [AuthController::class, 'user_logout'])->name('user_logout');

        // category
        Route::get('/category-list', [CategoryController::class, 'view'])->name('view_category');
        Route::post('/category-save', [CategoryController::class, 'save'])->name('save_category');
        Route::post('/category-delete', [CategoryController::class, 'destroy'])->name('delete_category');

        // products
        Route::get('/product-list', [ProductsController::class, 'index'])->name('product_list');
        Route::get('/get-variant-values/{attribute_id}', [ProductsController::class, 'getVariantValues']);
        Route::get('/get-secondary-values/{attribute_id}', [ProductsController::class, 'getSecondaryValues']);
        Route::post('/save-product', [ProductsController::class, 'saveProduct'])->name('save_product');
        Route::post('/delete-product', [ProductsController::class, 'deleteProduct'])->name('delete_product');

        //banner
        Route::get('/banner-list', [BannerController::class, 'view'])->name('view_banner');
        Route::post('/banner-save', [BannerController::class, 'save'])->name('save_banner');
        Route::post('/banner-delete', [BannerController::class, 'destroy'])->name('delete_banner');

        //attribute values
        Route::get('/attribute-list', [AttributeValueController::class, 'view'])->name('view_attribute');
        Route::post('/attribute-save', [AttributeValueController::class, 'save'])->name('save_attribute');

        //coupon
        Route::get('/coupon-list', [CouponController::class, 'view'])->name('view_coupon');
        Route::post('/coupon-save', [CouponController::class, 'save'])->name('save_coupon');
        Route::post('/coupon-delete', [CouponController::class, 'destroy'])->name('delete_coupon');

        //wallet bonus
        Route::get('/wallet-bonus-list', [WalletBonusController::class, 'view'])->name('view_wallet_bonus');
        Route::post('/wallet-bonus-save', [WalletBonusController::class, 'save'])->name('save_wallet_bonus');
        Route::post('/wallet-bonus-delete', [WalletBonusController::class, 'destroy'])->name('delete_wallet_bonus');

        // ticket lists
        Route::get('/ticket-lists', [TicketController::class, 'index'])->name('ticket_lists');
        Route::post('/ticket-save', [TicketController::class, 'saveTicket'])->name('ticket_save');

        //orders
        Route::prefix('orders')->controller(OrderController::class)->group(function () {
            Route::get('/list', 'view')->name('view.orders');
            Route::post('/update-status', 'updateStatus')->name('update.order.status');
        });

        Route::prefix('shipping')->controller(ShippingController::class)->group(function () {
            Route::get('/list', 'view')->name('view.shipping');
            Route::post('/save', 'save')->name('save.shipping');
            Route::post('/delete', 'destroy')->name('delete.shipping');
            Route::post('/tax/save', 'taxSave')->name('save.tax');
            Route::get('/tax/get', 'getTax')->name('get.tax');
            Route::get('/amount/get', 'getShipping')->name('get.shipping');
            Route::post('/amount/save', 'shippingSave')->name('save.default.shipping');
            Route::get('/amount/platform_fee/get', 'getPlatformFee')->name('get.platform_fee');
            Route::post('/amount/platform_fee/save', 'platformfeeSave')->name('save.platform_fee');
        });

        // notification
        Route::get('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    });
});
