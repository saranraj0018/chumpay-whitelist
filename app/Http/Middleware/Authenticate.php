<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
    */

    protected function redirectTo($request): ?string
    {
         if (! $request->expectsJson()) {

            // Redirect for Admin users
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            // Default redirect for other guests
            return route('admin.login');  // If you don't have public login, you may remove this
        }

        return null;
    }
}
