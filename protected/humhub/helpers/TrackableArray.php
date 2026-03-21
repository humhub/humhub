<?php

namespace humhub\helpers;

/**
 * TrackableArray
 * Wraps an array to provide "dirty checking" (tracks if its content was modified).
 */
class TrackableArray implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $data = [];
    private bool $isDirty = false;

    public function __construct(array $initialData = [])
    {
        $this->data = $initialData;
    }

    public function hasChanged(): bool
    {
        return $this->isDirty;
    }

    public function resetChanged(): void
    {
        $this->isDirty = false;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            // Append new value ($array[] = $value)
            $this->data[] = $value;
            $this->isDirty = true;
        } elseif (!array_key_exists($offset, $this->data) || $this->data[$offset] !== $value) {
            // Set by key ($array['key'] = $value) - only mark dirty if value changed
            $this->data[$offset] = $value;
            $this->isDirty = true;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
            $this->isDirty = true;
        }
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    public function clear(): void
    {
        // Only mark as dirty if there was actually something to clear
        if (!empty($this->data)) {
            $this->data = [];
            $this->isDirty = true;
        }
    }
}
