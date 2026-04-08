<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field;

final class FieldManager
{
    /**
     * @var array<string, \Concrete\Core\Block\Manifest\Field\FieldInterface>
     */
    protected $fields = [];

    public function register(FieldInterface $field): void
    {
        $this->fields[$field->getHandle()] = $field;
    }

    public function has(string $handle): bool
    {
        return isset($this->fields[$handle]);
    }

    public function get(string $handle): ?FieldInterface
    {
        return $this->fields[$handle] ?? null;
    }

    /**
     * @return array<string, \Concrete\Core\Block\Manifest\Field\FieldInterface>
     */
    public function all(): array
    {
        return $this->fields;
    }
}
