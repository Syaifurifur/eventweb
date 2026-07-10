<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateLegacy
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
