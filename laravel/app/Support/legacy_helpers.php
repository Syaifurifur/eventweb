<?php

use App\Support\LegacyDatabase;
use App\Support\LegacyMailer;
use GuzzleHttp\Client;

if (! function_exists('site_url')) {
    function site_url(string $path = ''): string { return url(ltrim($path, '/')); }
}

if (! function_exists('base_url')) {
    function base_url(string $path = ''): string { return asset(ltrim($path, '/')); }
}

if (! function_exists('esc')) {
    function esc(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

if (! function_exists('db_connect')) {
    function db_connect(): LegacyDatabase { return new LegacyDatabase(); }
}

if (! function_exists('service')) {
    function service(string $name, array $config = [], bool $shared = true): mixed
    {
        return match ($name) {
            'email' => new LegacyMailer($config),
            'curlrequest' => new Client($config),
            'request' => request(),
            'response' => response(),
            'session' => session(),
            default => app($name),
        };
    }
}

if (! function_exists('log_message')) {
    function log_message(string $level, string $message): void { logger()->log($level, $message); }
}
