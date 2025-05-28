<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // Return null or don't return anything here to prevent redirect
        return null;
    }

    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            abort(401, 'Unauthenticated.');
        }

        // Do nothing or abort with 403 instead of redirect
        // abort(403, 'Access denied.');
    }
}
