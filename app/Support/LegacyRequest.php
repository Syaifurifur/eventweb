<?php

namespace App\Support;

use Illuminate\Http\Request;

final class LegacyRequest
{
    public function __construct(private readonly Request $request)
    {
    }

    public function getPost(?string $key = null): mixed
    {
        return $key === null ? $this->request->post() : $this->request->input($key);
    }

    public function getGet(?string $key = null): mixed
    {
        return $key === null ? $this->request->query() : $this->request->query($key);
    }

    public function getFile(string $key): ?LegacyUploadedFile
    {
        $file = $this->request->file($key);
        return $file ? new LegacyUploadedFile($file) : null;
    }

    public function getIPAddress(): string { return (string) $this->request->ip(); }
    public function getUserAgent(): string { return (string) $this->request->userAgent(); }
    public function all(): array { return $this->request->all(); }
}
