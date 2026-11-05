<?php

namespace Config;

final class Gemini
{
    public string $apiKey;
    public string $model;
    public string $baseURL;
    public int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) env('GEMINI_API_KEY', '');
        $this->model = (string) env('GEMINI_MODEL', 'gemini-2.5-flash');
        $this->baseURL = rtrim((string) env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->timeout = (int) env('GEMINI_TIMEOUT', 90);
    }
}
