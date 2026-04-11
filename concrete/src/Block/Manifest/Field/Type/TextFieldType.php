<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field\Type;

use Concrete\Core\Block\Manifest\Field\AbstractFieldType;
use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Http\Request;

final class TextFieldType extends AbstractFieldType
{
    public function getHandle(): string
    {
        return 'text';
    }

    public function getComponent(): string
    {
        return 'ComposableEditorTextField';
    }

    public function normalizeDefinition(array $definition): array
    {
        $definition = parent::normalizeDefinition($definition);
        $definition['default'] = isset($definition['default']) ? (string) $definition['default'] : '';

        return $definition;
    }

    public function extractValueFromRequest(array $requestArgs, FieldDefinition $field, ?Request $request = null): string
    {
        return (string) $this->extractScalarValueFromRequest($requestArgs, $field);
    }

    public function serializeValue($submittedValue, array $definition, ?Request $request = null): string
    {
        return (string) $submittedValue;
    }
}
