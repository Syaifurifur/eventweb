<?php

namespace App\Filters;

use App\Libraries\EventHubCrmBridge;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CrmAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session('crmLoggedIn')) {
            return null;
        }

        if ((new EventHubCrmBridge())->signIn()) {
            return null;
        }

        return redirect()->to(site_url('crm/login'))->with('error', 'Silakan login CRM terlebih dahulu.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
