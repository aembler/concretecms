<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

final class FieldReference implements FormLayoutElementInterface
{
    /**
     * @var string
     */
    protected $fieldId;

    public function __construct(string $fieldId)
    {
        $this->fieldId = $fieldId;
    }

    public function getType(): string
    {
        return 'fieldref';
    }

    public function getFieldId(): string
    {
        return $this->fieldId;
    }

    /**
     * @return array{type: string, fieldId: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'fieldId' => $this->fieldId,
        ];
    }
}
