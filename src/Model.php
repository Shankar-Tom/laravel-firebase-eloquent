<?php

namespace Shankar\FirebaseDb;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

abstract class Model
{
    /**
     * Path/collection in Realtime DB
     * Example: 'users'
     */
    protected string $collection;

    /**
     * Whitelisted attributes for create/update
     */
    protected array $fillable = [];

    /**
     * Use timestamps
     */
    protected bool $timestamps = true;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function getCollection(): string
    {
        return $this->collection;
    }


    public static function query(): QueryBuilder
    {
        return new QueryBuilder(new static());
    }

    public static function all()
    {
        return static::query()->get();
    }

    public static function find(string $id): ?static
    {
        $inst = new static();
        $snap = firebase_db()->getReference($inst->collection . '/' . $id)->getValue();
        return $snap ? new static(array_merge(['id' => $id], $snap)) : null;
    }

    public static function create(array $attributes): static
    {
        $inst = new static();

        $diff = array_diff(array_keys($attributes), $inst->fillable);
        if ($diff) {
            throw new \Exception('Unknown field: ' . implode(', ', $diff));
        }

        $data = array_intersect_key($attributes, array_flip($inst->fillable));

        $id = Str::uuid()->toString();
        if ($inst->timestamps) {
            $now = now()->toDateTimeString();
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        firebase_db()->getReference($inst->collection . '/' . $id)->set($data);
        return static::find($id);
    }

    public function update(array $attributes): static
    {
        $inst = $this;
        $data = array_intersect_key($attributes, array_flip($inst->fillable));
        if ($inst->timestamps) {
            $data['updated_at'] = now()->toDateTimeString();
        }
        firebase_db()->getReference($inst->collection . '/' . $this->id)->update($data);
        return static::find($this->id);
    }

    public function delete(): bool
    {
        firebase_db()->getReference($this->collection . '/' . $this->id)->remove();
        return true;
    }
}
