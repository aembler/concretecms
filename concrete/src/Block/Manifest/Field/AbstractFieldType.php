<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field;

use Concrete\Core\Http\Request;

abstract class AbstractFieldType implements FieldInterface
{
    public function getComponentProps(): array
    {
        return [];
    }

    public function jsonSerialize(): array
    {
        return [
            'handle' => $this->getHandle(),
            'component' => $this->getComponent(),
            'componentProps' => $this->getComponentProps(),
        ];
    }

    public function normalizeDefinition(array $definition): array
    {
        return $definition;
    }

    /**
     * @param mixed $submittedValue
     *
     * @return mixed
     */
    public function serializeValue($submittedValue, array $definition, ?Request $request = null)
    {
        return $submittedValue;
    }
}
