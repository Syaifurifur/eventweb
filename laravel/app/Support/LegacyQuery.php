<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LegacyQuery
{
    protected Builder $query;
    protected array $groups = [];
    protected array $pendingSet = [];

    public function __construct(
        protected $table,
        public readonly LegacyDatabase $database = new LegacyDatabase(),
    ) {
        $this->resetQuery();
    }

    protected function newQuery(): Builder
    {
        return DB::table($this->table);
    }

    protected function resetQuery(): void
    {
        $this->query = $this->newQuery();
        $this->groups = [];
        $this->pendingSet = [];
    }

    protected function activeQuery(): Builder
    {
        if ($this->groups === []) {
            return $this->query;
        }

        return $this->groups[array_key_last($this->groups)]['nested'];
    }

    protected function closeGroups(): void
    {
        while ($this->groups !== []) {
            $this->groupEnd();
        }
    }

    public function select(string|array $fields, bool $escape = true): static
    {
        $fields = is_array($fields) ? implode(', ', $fields) : $fields;
        $this->query->selectRaw($fields);

        return $this;
    }

    public function join(string $table, string $condition, string $type = 'inner'): static
    {
        if (! preg_match('/^\s*([^\s]+)\s*(=|!=|<>|>=|<=|>|<)\s*([^\s]+)\s*$/', $condition, $match)) {
            throw new \InvalidArgumentException('Kondisi join tidak didukung: ' . $condition);
        }

        $method = strtolower($type) === 'left' ? 'leftJoin' : (strtolower($type) === 'right' ? 'rightJoin' : 'join');
        $this->query->{$method}($table, $match[1], $match[2], $match[3]);

        return $this;
    }

    public function where(string|array $field, mixed $value = null): static
    {
        return $this->addWhere($field, $value, 'and');
    }

    public function orWhere(string|array $field, mixed $value = null): static
    {
        return $this->addWhere($field, $value, 'or');
    }

    protected function addWhere(string|array $field, mixed $value, string $boolean): static
    {
        if (is_array($field)) {
            foreach ($field as $key => $item) {
                $this->addWhere((string) $key, $item, $boolean);
                $boolean = 'and';
            }

            return $this;
        }

        [$column, $operator] = $this->splitOperator($field);
        $query = $this->activeQuery();

        if ($value === null) {
            $not = in_array($operator, ['!=', '<>', 'IS NOT'], true);
            $method = $boolean === 'or'
                ? ($not ? 'orWhereNotNull' : 'orWhereNull')
                : ($not ? 'whereNotNull' : 'whereNull');
            $query->{$method}($column);
        } elseif (str_contains($column, '(')) {
            $prefix = $boolean === 'or' ? 'orWhereRaw' : 'whereRaw';
            $query->{$prefix}($column . ' ' . $operator . ' ?', [$value]);
        } else {
            $query->where($column, $operator, $value, $boolean);
        }

        return $this;
    }

    protected function splitOperator(string $field): array
    {
        if (preg_match('/^(.+?)\s+(IS NOT|IS|!=|<>|>=|<=|>|<|=)$/i', trim($field), $match)) {
            return [trim($match[1]), strtoupper($match[2])];
        }

        return [trim($field), '='];
    }

    public function whereIn(string $field, array $values): static
    {
        $this->activeQuery()->whereIn($field, $values);
        return $this;
    }

    public function whereNotIn(string $field, array $values): static
    {
        $this->activeQuery()->whereNotIn($field, $values);
        return $this;
    }

    public function like(string $field, string $value): static
    {
        $this->activeQuery()->where($field, 'like', '%' . $value . '%');
        return $this;
    }

    public function orLike(string $field, string $value): static
    {
        $this->activeQuery()->orWhere($field, 'like', '%' . $value . '%');
        return $this;
    }

    public function groupStart(): static
    {
        return $this->startGroup('and');
    }

    public function orGroupStart(): static
    {
        return $this->startGroup('or');
    }

    protected function startGroup(string $boolean): static
    {
        $parent = $this->activeQuery();
        $this->groups[] = [
            'parent' => $parent,
            'nested' => $parent->forNestedWhere(),
            'boolean' => $boolean,
        ];

        return $this;
    }

    public function groupEnd(): static
    {
        if ($this->groups === []) {
            return $this;
        }

        $group = array_pop($this->groups);
        $group['parent']->addNestedWhereQuery($group['nested'], $group['boolean']);

        return $this;
    }

    public function orderBy(string $field, string $direction = '', bool $escape = true): static
    {
        $direction = strtoupper(trim($direction));
        if (! $escape || str_contains($field, '(') || ! in_array($direction, ['ASC', 'DESC'], true)) {
            $this->query->orderByRaw(trim($field . ' ' . $direction));
        } else {
            $this->query->orderBy($field, $direction);
        }

        return $this;
    }

    public function limit(int $limit, int $offset = 0): static
    {
        $this->query->limit($limit)->offset($offset);
        return $this;
    }

    public function set(string|array $field, mixed $value = null): static
    {
        if (is_array($field)) {
            $this->pendingSet = array_merge($this->pendingSet, $field);
        } else {
            $this->pendingSet[$field] = $value;
        }

        return $this;
    }

    public function get(?int $limit = null, int $offset = 0): LegacyResult
    {
        if ($limit !== null) {
            $this->limit($limit, $offset);
        }
        $this->closeGroups();
        $rows = $this->query->get()->map(static fn ($row): array => (array) $row)->all();
        $this->resetQuery();

        return new LegacyResult($rows);
    }

    public function first(): ?array
    {
        $this->closeGroups();
        $row = $this->query->first();
        $this->resetQuery();

        return $row ? (array) $row : null;
    }

    public function findAll(?int $limit = null, int $offset = 0): array
    {
        return $this->get($limit, $offset)->getResultArray();
    }

    public function countAllResults(): int
    {
        $this->closeGroups();
        $count = $this->query->count();
        $this->resetQuery();

        return $count;
    }

    public function insert(array $data, bool $returnID = true): int|bool
    {
        $id = (int) DB::table($this->table)->insertGetId($data);
        $this->database->recordInsert($id);
        $this->resetQuery();

        return $returnID ? ($id > 0 ? $id : false) : $id > 0;
    }

    public function insertBatch(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }
        $ok = DB::table($this->table)->insert($rows);
        $count = $ok ? count($rows) : 0;
        $this->database->recordAffected($count);

        return $count;
    }

    public function update(mixed $idOrData = null, ?array $data = null): bool
    {
        if (is_array($idOrData) && $data === null) {
            $data = $idOrData;
        } elseif ($idOrData !== null && $data !== null) {
            $this->where('id', $idOrData);
        }
        $data ??= $this->pendingSet;
        $this->closeGroups();
        $affected = $this->query->update($data);
        $this->database->recordAffected($affected);
        $this->resetQuery();

        return $affected >= 0;
    }

    public function delete(mixed $id = null): bool
    {
        if ($id !== null) {
            $this->where('id', $id);
        }
        $this->closeGroups();
        $affected = $this->query->delete();
        $this->database->recordAffected($affected);
        $this->resetQuery();

        return $affected >= 0;
    }
}
