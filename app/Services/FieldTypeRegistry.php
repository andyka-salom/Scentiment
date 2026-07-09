<?php

namespace App\Services;

use App\Contracts\FieldTypeInterface;
use InvalidArgumentException;

class FieldTypeRegistry
{
    protected array $types = [];

    public function register(string $name, FieldTypeInterface $fieldType): void
    {
        $this->types[$name] = $fieldType;
    }

    public function get(string $name): FieldTypeInterface
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("Field type '{$name}' is not registered.");
        }

        return $this->types[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }

    public function all(): array
    {
        return $this->types;
    }
}
