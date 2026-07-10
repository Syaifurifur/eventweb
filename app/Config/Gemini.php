<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Gemini extends BaseConfig
{
    public string $apiKey = '';
    public string $model = 'gemini-2.5-flash';
    public string $baseURL = 'https://generativelanguage.googleapis.com/v1beta';
    public int $timeout = 90;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = (string) env('GEMINI_API_KEY', '');
        $this->model = (string) env('GEMINI_MODEL', $this->model);
        $this->baseURL = rtrim((string) env('GEMINI_BASE_URL', $this->baseURL), '/');
        $this->timeout = (int) env('GEMINI_TIMEOUT', $this->timeout);
    }
}
