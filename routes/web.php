<?php

use App\Http\Controllers\web\AddressController;
use App\Http\Controllers\web\AuthController;
use App\Http\Controllers\web\CartController;
use App\Http\Controllers\web\HomeController;
use App\Http\Controllers\web\NotificationController;
use App\Http\Controllers\web\OrderController;
use App\Http\Controllers\web\OrderInvoiceController;
use App\Http\Controllers\web\ProfileController;
use App\Http\Controllers\web\ReviewController;
use App\Http\Controllers\web\ShopController;
use App\Http\Controllers\web\TicketController;
use App\Http\Controllers\web\WishlistController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/admin.php';

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/send-otp', [AuthController::class, 'sendOtp'])->name('send.otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify.otp');
Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register.user');
Route::post('/logout', [AuthController::class, 'logout'])->name('web_user_logout');


// Route::get('/home', function () {
//     return view('frontend.home');
// });

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/shop/single-product/{id}', [ShopController::class, 'singleProduct'])->name('single_product');

Route::get('/shop/cart', [ShopController::class, 'cart'])->name('shop.cart');
Route::get('/shop/delivery', [ShopController::class, 'delivery'])->name('shop.delivery');
Route::get('/shop/success', [ShopController::class, 'success'])->name('shop.success');

//cart
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('add_toCart');
Route::post('/cart/detail', [CartController::class, 'cartDetail'])->name('cart_details');
Route::post('/cart/delivery-address', [CartController::class, 'saveDeliveryAddress'])->name('cart.delivery_address.save');
Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
Route::post('/cart/select-all', [CartController::class, 'selectAllForCheckout'])->name('cart.select_all');

//coupon lists
Route::get('/coupons/list', [CartController::class, 'listCoupons'])->name('coupons.list');
Route::post('/coupons/remove', [CartController::class, 'removeCoupon'])->name('coupons.remove');

Route::post('/review/store/{product}', [ReviewController::class, 'store'])->name('review.store');

//address
Route::get('/address/list', [AddressController::class, 'addressList'])->name('address.list');
Route::post('/address/save', [AddressController::class, 'saveAddress'])->name('delivery_address.save');
Route::post('/address/set-default', [AddressController::class, 'setDefaultAddress'])->name('address.set_default');
Route::post('/address/delete', [AddressController::class, 'deleteAddress'])->name('address.delete');

//order
Route::post('/order/create', [OrderController::class, 'createOrder'])->name('order.create');
Route::post('/order/save', [OrderController::class, 'saveOrder'])->name('order.save');
Route::post('/order/verify-payment', [OrderController::class, 'verifyPayment'])->name('order.verify');
Route::get('/shop/payment/return', [OrderController::class, 'paymentReturn'])->name('payment.return');

//buy now
Route::get('/shop/buy-now/{id}', [ShopController::class, 'buyNow'])->name('shop.buynow');

Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/whishlist', [WishlistController::class, 'index'])->name('whishlist');


Route::get('/about-us', function () {
    return view('frontend.about');
});

Route::get('/contact-us', function () {
    return view('frontend.contactus.index');
});


Route::middleware(['auth'])->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('profile');
    Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    Route::get('/orders/order-status/{order}', [ProfileController::class, 'orderStatus'])->name('profile.order.status');
    Route::get('/orders/{order}/invoice/download', [OrderInvoiceController::class, 'download'])
        ->name('orders_invoice_download');
    //notification
    Route::get('/notification-list', [NotificationController::class, 'notificationList'])->name('notification_list');
    Route::get('/manage-address', fn() => view('frontend.profile.manageaddress.manageaddress'))
        ->name('profile.manage-address');
    //support help
    Route::get('support-help', [TicketController::class, 'index'])->name('support_help_lists');
    Route::post('ticket-save', [TicketController::class, 'ticketSave'])->name('support_help_save');
});
