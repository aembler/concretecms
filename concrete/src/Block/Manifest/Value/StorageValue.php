<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Value;

use Concrete\Core\Block\Manifest\FieldDefinition;

final class StorageValue implements \JsonSerializable
{

    public function __construct(
        public readonly string $schemaVersion,
        public array $values = [],
        public array $meta = [],
    ) {}

    public function addValue(FieldDefinition $field, mixed $value): self
    {
        $this->values[$field->getId()] = $value;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'values' => $this->values,
            'meta' => $this->meta,
            'schemaVersion' => $this->schemaVersion,
        ];
    }

}
