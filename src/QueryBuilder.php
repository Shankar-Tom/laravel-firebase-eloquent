<?php

namespace Shankar\FirebaseDb;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class QueryBuilder
{
    protected Model $model;

    protected array $wheres = [];
    protected array $whereIns = [];
    protected ?string $orderKey = null;
    protected string $orderDirection = 'asc';
    protected ?int $limitValue = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function where(string $key, string $operator, $value): static
    {
        $this->wheres[] = compact('key', 'operator', 'value');
        return $this;
    }

    public function whereIn(string $key, array $values): static
    {
        $this->whereIns[] = compact('key', 'values');
        return $this;
    }

    public function orderBy(string $key, string $direction = 'asc'): static
    {
        $this->orderKey = $key;
        $this->orderDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        return $this;
    }

    public function limit(int $value): static
    {
        $this->limitValue = $value;
        return $this;
    }

    public function get(): Collection
    {
        // Fetch entire collection then filter in memory (simple & safe default)
        $snapshot = firebase_db()->getReference($this->model->getCollection())->getValue();

        $records = collect($snapshot ?: [])->map(function ($row, $id) {
            $modelClass = get_class($this->model);
            return new $modelClass(array_merge(['id' => $id], $row));
        });

        // where filters
        foreach ($this->wheres as $w) {
            $records = $records->filter(function ($item) use ($w) {
                $val = $item->{$w['key']} ?? null;
                return match ($w['operator']) {
                    '=', '==' => $val == $w['value'],
                    '!=', '<>' => $val != $w['value'],
                    '>', '<', '>=', '<=' => $this->compare($val, $w['operator'], $w['value']),
                    default => false
                };
            });
        }

        // whereIn
        foreach ($this->whereIns as $in) {
            $records = $records->filter(function ($item) use ($in) {
                $val = $item->{$in['key']} ?? null;
                return in_array($val, $in['values'], true);
            });
        }

        // order
        if ($this->orderKey) {
            $desc = $this->orderDirection === 'desc';
            $records = $records->sortBy(fn($x) => $x->{$this->orderKey} ?? null, SORT_REGULAR, $desc);
        }

        // limit
        if ($this->limitValue) {
            $records = $records->take($this->limitValue);
        }

        return $records->values();
    }

    public function first()
    {
        return $this->limit(1)->get()->first();
    }

    public function paginate(int $perPage = null, string $pageName = 'page', int $page = null): LengthAwarePaginator
    {
        $perPage = $perPage ?: (int) config('firebase-db.per_page', 15);
        $page = $page ?: (int) request()->input($pageName, 1);

        $items = $this->get();
        $total = $items->count();

        $sliced = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    protected function compare($a, string $op, $b): bool
    {
        return match ($op) {
            '>' => $a > $b,
            '<' => $a < $b,
            '>=' => $a >= $b,
            '<=' => $a <= $b,
            default => false
        };
    }
}
