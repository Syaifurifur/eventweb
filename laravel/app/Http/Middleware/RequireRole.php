<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! session('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }
        if ($roles !== [] && ! in_array(session('user.role'), $roles, true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Role Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
