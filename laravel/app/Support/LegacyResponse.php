<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

final class LegacyResponse
{
    private array $headers = [];

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): Response
    {
        $response = response($body, 200, $this->headers);
        $this->headers = [];
        return $response;
    }

    public function download(string $path, mixed $name = null): LegacyDownloadResponse
    {
        return new LegacyDownloadResponse($path, $this->headers);
    }
}

final class LegacyDownloadResponse
{
    public function __construct(private readonly string $path, private readonly array $headers = [])
    {
    }

    public function setFileName(string $name): Response
    {
        return response()->download($this->path, $name, $this->headers);
    }
}
