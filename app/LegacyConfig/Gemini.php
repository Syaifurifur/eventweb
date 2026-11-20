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
        $this->apiKey = self::readEnv('GEMINI_API_KEY', '');
        $this->model = self::readEnv('GEMINI_MODEL', 'gemini-2.5-flash');
        $this->baseURL = rtrim(self::readEnv('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->timeout = (int) self::readEnv('GEMINI_TIMEOUT', '90');
    }

    private static function readEnv(string $key, string $default): string
    {
        $val = env($key);
        if ($val !== null && $val !== '') {
            return (string) $val;
        }
        $g = getenv($key);
        if ($g !== false && $g !== '') {
            return (string) $g;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }

        return $default;
    }
}
