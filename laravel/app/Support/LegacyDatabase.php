<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LegacyDatabase
{
    private int $insertId = 0;
    private int $affectedRows = 0;
    private bool $transactionStatus = true;

    public function table(string $table): LegacyQuery
    {
        return new LegacyQuery($table, $this);
    }

    public function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function fieldExists(string $field, string $table): bool
    {
        return Schema::hasColumn($table, $field);
    }

    public function insertID(): int
    {
        return $this->insertId ?: (int) DB::getPdo()->lastInsertId();
    }

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }

    public function recordInsert(int $id): void
    {
        $this->insertId = $id;
        $this->affectedRows = 1;
    }

    public function recordAffected(int $rows): void
    {
        $this->affectedRows = $rows;
    }

    public function transStart(): void
    {
        $this->transactionStatus = true;
        DB::beginTransaction();
    }

    public function transComplete(): void
    {
        try {
            DB::commit();
        } catch (\Throwable $error) {
            $this->transactionStatus = false;
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $error;
        }
    }

    public function transStatus(): bool
    {
        return $this->transactionStatus;
    }
}
