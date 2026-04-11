<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field\Type;

use Concrete\Core\Block\Manifest\Field\AbstractFieldType;
use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Http\Request;

final class ColorFieldType extends AbstractFieldType
{
    public function getHandle(): string
    {
        return 'color';
    }

    public function getComponent(): string
    {
        return 'ComposableEditorColorField';
    }

    public function normalizeDefinition(array $definition): array
    {
        $definition = parent::normalizeDefinition($definition);
        if (isset($definition['default']) && is_string($definition['default']) && $definition['default'] !== '') {
            $definition['default'] = strtoupper($definition['default']);
        } else {
            $definition['default'] = '';
        }

        return $definition;
    }

    public function extractValueFromRequest(array $requestArgs, FieldDefinition $field, ?Request $request = null): string
    {
        return strtoupper((string) $this->extractScalarValueFromRequest($requestArgs, $field));
    }

    public function serializeValue($submittedValue, array $definition, ?Request $request = null): string
    {
        return strtoupper((string) $submittedValue);
    }
}
