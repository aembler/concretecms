<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Field\FieldInterface;

final class FieldDefinition implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array<string, mixed>
     */
    protected $definition;

    /**
     * @var \Concrete\Core\Block\Manifest\Field\FieldInterface|null
     */
    protected $fieldType;

    /**
     * @param array<string, mixed> $definition
     */
    public function __construct(string $id, string $type, string $label, array $definition = [], ?FieldInterface $fieldType = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->label = $label;
        $this->definition = $definition;
        $this->fieldType = $fieldType;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    public function getFieldType(): ?FieldInterface
    {
        return $this->fieldType;
    }

    public function hasKnownFieldType(): bool
    {
        return $this->fieldType instanceof FieldInterface;
    }

    /**
     * @return array{id: string, type: string, label: string, definition: array<string, mixed>, fieldType: \Concrete\Core\Block\Manifest\Field\FieldInterface|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->label,
            'definition' => $this->definition,
            'fieldType' => $this->fieldType,
        ];
    }
}
