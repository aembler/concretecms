<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Value;

use Concrete\Core\Block\Manifest\FieldDefinition;

final class ViewValue implements \JsonSerializable
{

    public function __construct(
        public array $values = [],
    ) {}

    public function addValue(FieldDefinition $field, mixed $value): self
    {
        $this->values[$field->getId()] = $field->getFieldType()->createViewValue($value, $field);
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'values' => $this->values,
        ];
    }

}
