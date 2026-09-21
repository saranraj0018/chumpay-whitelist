<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
    */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function (ViewContract $view) {

            if ($this->app->runningInConsole()) {
                $view->with([
                    'pendingOrderCount'            => 0,
                    'pendingTicketCount'           => 0,
                    'pendingNotificationCount'     => 0,
                    'userpendingOrderCount'        => 0,
                    'userpendingTicketCount'       => 0,
                    'userpendingNotificationCount' => 0,
                    'cartCount'                    => 0,
                ]);
                return;
            }

            $userId = Auth::id();
            $cartCount = 0;
            if ($userId) {
                $cart = Cache::get("cart_{$userId}", []);
                $cartCount = collect($cart)
                    ->filter(fn($item) => empty($item['is_buy_now']))
                    ->sum('quantity');
            }
            $view->with([
                'pendingOrderCount'        => Order::where('status', 1)->count(),
                'pendingTicketCount'       => Ticket::where('status', 'pending')->count(),
                'pendingNotificationCount' => Notification::where('status', 0)->count(),
                'cartCount' => $cartCount,
                'userpendingOrderCount'        => $userId ? Order::where('user_id', $userId)->where('status', 1)->count() : 0,
                'userpendingTicketCount'       => $userId ? Ticket::where('user_id', $userId)->where('status', 'pending')->count() : 0,
                'userpendingNotificationCount' => $userId ? Notification::where('user_id', $userId)->where('status', 0)->count() : 0,
            ]);
        });
    }
}
