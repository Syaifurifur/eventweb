<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireAccess
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! session('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session('user.role') !== 'Admin Sistem' && ! in_array($permission, session('user.permissions', []), true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses utama Anda tidak mengizinkan halaman tersebut.');
        }

        return $next($request);
    }
}
