<?php

namespace App\Http\Middleware;

use App\Libraries\EventHubCrmBridge;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateCrm
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('crmLoggedIn') && ! (new EventHubCrmBridge())->signIn()) {
            return redirect()->to(site_url('crm/login'))->with('error', 'Silakan login CRM terlebih dahulu.');
        }

        return $next($request);
    }
}
