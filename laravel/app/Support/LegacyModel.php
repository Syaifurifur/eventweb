<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class LegacyModel extends LegacyQuery
{
    private static array $columnCache = [];
    protected $table = '';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $allowedFields = [];
    protected $validationRules = [];
    protected array $validationErrors = [];
    protected bool $validationEnabled = true;
    protected bool $includeDeleted = false;
    public LegacyDatabase $db;

    public function __construct()
    {
        $this->db = new LegacyDatabase();
        parent::__construct($this->table, $this->db);
    }

    protected function newQuery(): \Illuminate\Database\Query\Builder
    {
        $query = DB::table($this->table);
        if ($this->useSoftDeletes && ! $this->includeDeleted && $this->hasColumn('deleted_at')) {
            $query->whereNull($this->table . '.deleted_at');
        }

        return $query;
    }

    protected function resetQuery(): void
    {
        $this->includeDeleted = false;
        parent::resetQuery();
    }

    public function withDeleted(): static
    {
        $this->includeDeleted = true;
        $softDeleteColumn = $this->table . '.deleted_at';
        $this->query->wheres = array_values(array_filter(
            $this->query->wheres ?? [],
            static fn (array $where): bool => ! (
                in_array($where['type'] ?? '', ['Null', 'NotNull'], true)
                && ($where['column'] ?? '') === $softDeleteColumn
            ),
        ));

        return $this;
    }

    public function builder(): LegacyQuery
    {
        return new LegacyQuery($this->table, $this->db);
    }

    public function find(mixed $id): ?array
    {
        return $this->where($this->table . '.' . $this->primaryKey, $id)->first();
    }

    public function insert(array $data, bool $returnID = true): int|bool
    {
        $data = $this->prepareData($data, true);
        if (! $this->validateData($data, false)) {
            $this->resetQuery();
            return false;
        }

        $id = parent::insert($data);

        return $returnID ? $id : $id !== false;
    }

    public function insertBatch(array $rows): int
    {
        $prepared = [];
        foreach ($rows as $row) {
            $row = $this->prepareData($row, true);
            if (! $this->validateData($row, false)) {
                return 0;
            }
            $prepared[] = $row;
        }

        return parent::insertBatch($prepared);
    }

    public function update(mixed $idOrData = null, ?array $data = null): bool
    {
        if (is_array($idOrData) && $data === null) {
            $data = $idOrData;
            $idOrData = null;
        }
        $data ??= $this->pendingSet;
        if ($idOrData !== null) {
            $data[$this->primaryKey] = $idOrData;
        }
        $data = $this->prepareData($data, false);
        if (! $this->validateData($data, true)) {
            $this->resetQuery();
            return false;
        }
        unset($data[$this->primaryKey]);

        return parent::update($idOrData, $data);
    }

    public function delete(mixed $id = null, bool $purge = false): bool
    {
        if ($id !== null) {
            $this->where($this->primaryKey, $id);
        }
        if ($this->useSoftDeletes && ! $purge && $this->hasColumn('deleted_at')) {
            return parent::update(['deleted_at' => date('Y-m-d H:i:s')]);
        }

        return parent::delete();
    }

    public function skipValidation(bool $skip = true): static
    {
        $this->validationEnabled = ! $skip;
        return $this;
    }

    public function errors(): array
    {
        return $this->validationErrors;
    }

    protected function prepareData(array $data, bool $insert): array
    {
        if ($this->allowedFields !== []) {
            $allowed = array_flip(array_merge($this->allowedFields, [$this->primaryKey]));
            $data = array_intersect_key($data, $allowed);
        }
        if ($this->useTimestamps) {
            $now = date('Y-m-d H:i:s');
            if ($insert && $this->hasColumn('created_at')) {
                $data['created_at'] ??= $now;
            }
            if ($this->hasColumn('updated_at')) {
                $data['updated_at'] = $now;
            }
        }

        return $data;
    }

    protected function hasColumn(string $column): bool
    {
        $key = $this->table . '.' . $column;
        return self::$columnCache[$key] ??= Schema::hasColumn($this->table, $column);
    }

    protected function validateData(array $data, bool $partial = false): bool
    {
        $this->validationErrors = [];
        if (! $this->validationEnabled || $this->validationRules === []) {
            $this->validationEnabled = true;
            return true;
        }

        foreach ($this->validationRules as $field => $rules) {
            if ($partial && ! array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field] ?? null;
            $items = explode('|', $rules);
            if (in_array('permit_empty', $items, true) && ($value === null || $value === '')) {
                continue;
            }
            foreach ($items as $rule) {
                if (! $this->passesRule($field, $value, $rule, $data)) {
                    $this->validationErrors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' tidak valid.';
                    break;
                }
            }
        }
        $this->validationEnabled = true;

        return $this->validationErrors === [];
    }

    protected function passesRule(string $field, mixed $value, string $rule, array $data): bool
    {
        if ($rule === 'required') return ! ($value === null || trim((string) $value) === '');
        if ($rule === 'valid_email') return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        if ($rule === 'is_natural_no_zero') return filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0;
        if ($rule === 'alpha_numeric_punct') return preg_match('/^[\pL\pN\s._@+\-]+$/u', (string) $value) === 1;
        if (preg_match('/^min_length\[(\d+)]$/', $rule, $m)) return mb_strlen((string) $value) >= (int) $m[1];
        if (preg_match('/^max_length\[(\d+)]$/', $rule, $m)) return mb_strlen((string) $value) <= (int) $m[1];
        if (preg_match('/^in_list\[(.*)]$/', $rule, $m)) return in_array((string) $value, explode(',', $m[1]), true);
        if (preg_match('/^is_unique\[([^.]+)\.([^,]+),([^,]+),\{([^}]+)}]$/', $rule, $m)) {
            $query = DB::table($m[1])->where($m[2], $value);
            $ignore = $data[$m[4]] ?? null;
            if ($ignore !== null && $ignore !== '') $query->where($m[3], '!=', $ignore);
            return ! $query->exists();
        }

        return true;
    }
}
