<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

final class LegacyUploadedFile
{
    public function __construct(private readonly UploadedFile $file)
    {
    }

    public function isValid(): bool { return $this->file->isValid(); }
    public function getError(): int { return $this->file->getError(); }
    public function getClientExtension(): string { return (string) $this->file->getClientOriginalExtension(); }
    public function getClientName(): string { return $this->file->getClientOriginalName(); }
    public function getSize(): int { return (int) $this->file->getSize(); }
    public function getTempName(): string { return $this->file->getPathname(); }
    public function guessExtension(): ?string { return $this->file->guessExtension(); }

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
}
