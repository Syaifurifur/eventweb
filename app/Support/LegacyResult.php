<?php

namespace App\Support;

final class LegacyResult
{
    public function __construct(private readonly array $rows)
    {
    }

    public function getResultArray(): array
    {
        return $this->rows;
    }

    public function getRowArray(): ?array
    {
        return $this->rows[0] ?? null;
    }
}
