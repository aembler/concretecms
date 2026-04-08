<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Error\ManifestError;
use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use SimpleXMLElement;

final class FieldDefinitionParser
{
    /**
     * @var \Concrete\Core\Block\Manifest\Field\FieldManager
     */
    protected $fieldManager;

    public function __construct(FieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     *
     * @return array<string, \Concrete\Core\Block\Manifest\FieldDefinition>
     */
    public function parseFieldGroups(SimpleXMLElement $parent, array &$errors): array
    {
        $result = [];
        $fieldGroups = $parent->xpath('./fields');
        if (!is_array($fieldGroups)) {
            return $result;
        }

        foreach ($fieldGroups as $fieldGroup) {
            if (!($fieldGroup instanceof SimpleXMLElement)) {
                continue;
            }

            $prefix = trim((string) ($fieldGroup['prefix'] ?? ''));
            foreach ($fieldGroup->xpath('./field') ?: [] as $fieldNode) {
                if (!($fieldNode instanceof SimpleXMLElement)) {
                    continue;
                }

                $rawFieldId = $this->getRequiredAttribute($fieldNode, 'id', '<field>');
                $fieldId = $this->buildFieldId($prefix, $rawFieldId);
                if (isset($result[$fieldId])) {
                    throw new MalformedManifestException(sprintf('Duplicate field id "%s" found in manifest.', $fieldId));
                }

                $fieldTypeHandle = $this->getRequiredAttribute($fieldNode, 'type', sprintf('<field id="%s">', $fieldId));
                $label = trim((string) ($fieldNode['label'] ?? $fieldId));
                $definition = $this->collectAttributes($fieldNode);
                $definition['id'] = $fieldId;
                $definition['rawId'] = $rawFieldId;
                if ($prefix !== '') {
                    $definition['prefix'] = $prefix;
                }

                $fieldType = $this->fieldManager->get($fieldTypeHandle);
                if ($fieldType !== null) {
                    $definition = $fieldType->normalizeDefinition($definition);
                } else {
                    $errors[] = new ManifestError(
                        'unknown_field_type',
                        sprintf('Unknown field type "%s" for field "%s".', $fieldTypeHandle, $fieldId),
                        [
                            'fieldId' => $fieldId,
                            'rawFieldId' => $rawFieldId,
                            'fieldType' => $fieldTypeHandle,
                            'prefix' => $prefix,
                        ]
                    );
                }

                $result[$fieldId] = new FieldDefinition($fieldId, $fieldTypeHandle, $label, $definition, $fieldType);
            }
        }

        return $result;
    }

    public function buildFieldId(string $prefix, string $fieldId): string
    {
        $prefix = trim($prefix);
        $fieldId = trim($fieldId);
        if ($prefix === '') {
            return $fieldId;
        }

        return $prefix . '.' . $fieldId;
    }

    protected function getRequiredAttribute(SimpleXMLElement $element, string $attributeName, string $elementLabel): string
    {
        $value = trim((string) ($element[$attributeName] ?? ''));
        if ($value === '') {
            throw new MalformedManifestException(sprintf('Missing required attribute "%s" on %s.', $attributeName, $elementLabel));
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    protected function collectAttributes(SimpleXMLElement $element): array
    {
        $attributes = [];
        foreach ($element->attributes() as $key => $value) {
            $attributes[(string) $key] = trim((string) $value);
        }

        return $attributes;
    }
}
