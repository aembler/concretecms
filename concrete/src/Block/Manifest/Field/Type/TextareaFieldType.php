<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field\Type;

use Concrete\Core\Block\Manifest\Field\AbstractFieldType;
use Concrete\Core\Http\Request;

final class TextareaFieldType extends AbstractFieldType
{
    public function getHandle(): string
    {
        return 'textarea';
    }

    public function getComponent(): string
    {
        return 'ManifestFieldTextarea';
    }

    public function normalizeDefinition(array $definition): array
    {
        $definition = parent::normalizeDefinition($definition);
        $definition['default'] = isset($definition['default']) ? (string) $definition['default'] : '';
        if (isset($definition['rows'])) {
            $definition['rows'] = max(1, (int) $definition['rows']);
        }

        return $definition;
    }

    public function serializeValue($submittedValue, array $definition, ?Request $request = null): string
    {
        return (string) $submittedValue;
    }
}
