<?php

use App\Http\Controllers\api\AddressController;
use App\Http\Controllers\api\ApiAuthController;
use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\CouponController;
use App\Http\Controllers\api\FCMController;
use App\Http\Controllers\api\HomeApiController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\ProductsController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\ReviewsController;
use App\Http\Controllers\api\TicketController;
use App\Http\Controllers\api\WalletController;
use App\Http\Controllers\api\WishListsController;
use App\Http\Controllers\FilterController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'user'], function () {
    Route::post('/register', [ApiAuthController::class, 'userRegister']);
    Route::post('/otp', [ApiAuthController::class, 'VerifyOtp']);
    Route::post('/login', [ApiAuthController::class, 'login']);

    Route::middleware('verify.jwt')->group(function () {
        Route::get('/profile', [ProfileController::class, 'index']);
        Route::post('/profile-update', [ProfileController::class, 'editUser']);
        // category
        Route::get('/category-list', [CategoryController::class, 'index']);
        Route::post('/category-products', [CategoryController::class, 'categoryProducts']);
        //Address
        Route::get('/address/list', [AddressController::class, 'index']);
        Route::post('/address/save', [AddressController::class, 'save']);
        Route::post('/address/update', [AddressController::class, 'update']);
        Route::post('/address/set-default', [AddressController::class, 'setDefaultAddress']);
        Route::post('/address/delete', [AddressController::class, 'delete']);
        //tickets
        Route::post('/create-ticket', [TicketController::class, 'saveTicket']);
        Route::get('/ticket-list', [TicketController::class, 'ticketLists']);
        //fcm tocken save
        Route::post('/fcm-token-save', [NotificationController::class, 'saveFCMToken']);
        //notification
        Route::get('/notification-list', [NotificationController::class, 'notificationList']);
        Route::delete('/notification-delete', [NotificationController::class, 'deleteNotification']);
        //read notification
        Route::post('/notification-read', [NotificationController::class, 'readNotification']);
        Route::post('/notification-read-all', [NotificationController::class, 'readNotificationAll']);
        //home
        Route::get('/home', [HomeApiController::class, 'index']);
        //hot deals
        Route::get('/hot-deals', [HomeApiController::class, 'hotDeals']);
        Route::get('/today-deals', [HomeApiController::class, 'todaySales']);
        Route::get('/dealof-the-day', [HomeApiController::class, 'todaySales']);
        //wish list
        Route::get('/wishlists', [WishListsController::class, 'index']);
        Route::post('/save-wishlists', [WishListsController::class, 'likeAndUnlike']);
        //get product details
        Route::post('/product-details', [ProductsController::class, 'getProductDetails']);
        //cart
        Route::get('cart', [CartController::class, 'getCart']);
        Route::post('/cart-add', [CartController::class, 'addToCart']);
        Route::post('/remove-cart', [CartController::class, 'removeCartItem']);
        Route::post('cart-detail', [CartController::class, 'cartDetail']);
        //coupon list
        Route::post('coupon-list', [CouponController::class, 'couponList']);
        Route::post('apply-coupon', [CouponController::class, 'applyCouponToCart']);
        Route::post('/coupon-delete', [CouponController::class, 'deleteCoupon']);
        //order
        Route::get('/all/orders', [OrderController::class, 'allOrders']);
        Route::post('/order', [OrderController::class, 'getOrders']);
        Route::post('/order-details', [OrderController::class, 'getOrderDetails']);
        Route::post('/create-order', [OrderController::class, 'createOrder']);
        Route::post('/order/save', [OrderController::class, 'saveOrder']);
        Route::post('/verify-payment', [OrderController::class, 'verifyPayment']);
        //reviews
        Route::post('reviews', [ReviewsController::class, 'productReviews']);
        Route::post('review-add', [ReviewsController::class, 'addReview']);

        //wallet
        Route::post('create-wallet-order', [WalletController::class, 'createWalletOrder']);
        Route::post('add-wallet', [WalletController::class, 'addBalance']);
        Route::post('search-products', [FilterController::class, 'searchProducts']);
        Route::post('save-wallet-order', [WalletController::class, 'saveWalletOrder']);

        //fcm_token save
        Route::post('/fcm-token-save', [FCMController::class, 'saveFCMToken']);
    });
});
