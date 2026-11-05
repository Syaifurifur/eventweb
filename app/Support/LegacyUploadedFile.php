<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

final class LegacyUploadedFile
{
    public function __construct(private readonly UploadedFile $file)
    {
    }

    public function isValid(): bool { return $this->file->isValid(); }
    public function hasMoved(): bool { return false; }
    public function getError(): int { return $this->file->getError(); }
    public function getClientExtension(): string { return (string) $this->file->getClientOriginalExtension(); }
    public function getExtension(): string { return (string) ($this->file->guessExtension() ?: $this->file->getClientOriginalExtension()); }
    public function getClientName(): string { return $this->file->getClientOriginalName(); }
    public function getName(): string { return $this->file->getClientOriginalName(); }
    public function getSize(): int { return (int) $this->file->getSize(); }
    public function getSizeByUnit(string $unit = 'b'): float|int
    {
        $bytes = (int) $this->file->getSize();
        return match (strtolower($unit)) {
            'kb' => round($bytes / 1024, 2),
            'mb' => round($bytes / 1048576, 2),
            'gb' => round($bytes / 1073741824, 2),
            default => $bytes,
        };
    }
    public function getTempName(): string { return $this->file->getPathname(); }
    public function guessExtension(): ?string { return $this->file->guessExtension(); }
    public function getClientMimeType(): string { return (string) ($this->file->getClientMimeType() ?: $this->file->getMimeType() ?: 'application/octet-stream'); }
    public function getMimeType(): string { return (string) ($this->file->getMimeType() ?: $this->file->getClientMimeType() ?: 'application/octet-stream'); }
    public function getRandomName(): string
    {
        $ext = $this->getClientExtension() ?: $this->guessExtension() ?: 'bin';
        return time() . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
    }

    public function move(string $directory, ?string $name = null, bool $overwrite = false): bool
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $target = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ($name ?: $this->getClientName());
        if ($overwrite && is_file($target)) {
            unlink($target);
        }
        $this->file->move($directory, $name ?: $this->getClientName());

        return true;
    }

    public function __call(string $method, array $arguments): mixed
    {
        return $this->file->{$method}(...$arguments);
    }
}

