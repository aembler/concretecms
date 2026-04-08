<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field\Type;

use Concrete\Core\Block\Manifest\Field\AbstractFieldType;
use Concrete\Core\Http\Request;

final class ColorFieldType extends AbstractFieldType
{
    public function getHandle(): string
    {
        return 'color';
    }

    public function getComponent(): string
    {
        return 'ManifestFieldColor';
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

    public function serializeValue($submittedValue, array $definition, ?Request $request = null): string
    {
        return strtoupper((string) $submittedValue);
    }
}
