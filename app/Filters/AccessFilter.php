<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session('user.role') === 'Admin Sistem') {
            return null;
        }

        $required = $arguments[0] ?? null;
        $permissions = session('user.permissions') ?? [];

        if ($required !== null && ! in_array($required, $permissions, true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses utama Anda tidak mengizinkan halaman tersebut.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
