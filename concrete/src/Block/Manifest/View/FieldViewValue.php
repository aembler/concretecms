<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\View;

use Concrete\Core\Block\Manifest\FieldDefinition;

class FieldViewValue implements \JsonSerializable
{
    /**
     * @param mixed $value
     */
    public function __construct(
        protected FieldDefinition $field,
        protected $value,
    ) {
    }

    public function getId(): string
    {
        return $this->field->getId();
    }

    public function getType(): string
    {
        return $this->field->getType();
    }

    public function getLabel(): string
    {
        return $this->field->getLabel();
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        return $this->field->getDefinition();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '';
    }

    public function __toString(): string
    {
        if ($this->value === null) {
            return '';
        }

        if (is_scalar($this->value)) {
            return (string) $this->value;
        }

        return '';
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'label' => $this->getLabel(),
            'definition' => $this->getDefinition(),
            'value' => $this->getValue(),
        ];
    }
}
