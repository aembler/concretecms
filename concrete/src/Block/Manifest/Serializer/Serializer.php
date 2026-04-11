<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Serializer;

use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\FieldDefinition;
use JsonException;
use Psr\Log\LoggerInterface;

class Serializer
{
    public function __construct(
        protected BlockManifest $manifest,
        protected LoggerInterface $logger,
    ) {
    }

    public function serializeFromRequest(array $requestArgs): string
    {
        $payload = [
            'version' => 1,
            'fields' => [],
            'design' => [],
            'meta' => [],
        ];

        foreach ($this->manifest->getFields() as $field) {
            if (!$field instanceof FieldDefinition) {
                continue;
            }

            $fieldType = $field->getFieldType();
            if ($fieldType === null) {
                $this->logger->notice(
                    'Skipping manifest field "{fieldId}" during serialization because its type "{fieldType}" is not registered.',
                    [
                        'fieldId' => $field->getId(),
                        'fieldType' => $field->getType(),
                        'blockType' => $this->manifest->getHandle(),
                    ]
                );
                continue;
            }

            $submittedValue = $fieldType->extractValueFromRequest($requestArgs, $field);
            $serializedValue = $fieldType->serializeValue($submittedValue, $field->getDefinition());
            $section = $this->resolveFieldEnvelopeSection($field);

            $payload[$section][$field->getId()] = $serializedValue;
        }

        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new \RuntimeException('Unable to serialize manifest block request payload to JSON.', 0, $e);
        }
    }

    protected function resolveFieldEnvelopeSection(FieldDefinition $field): string
    {
        return $this->isDesignField($field) ? 'design' : 'fields';
    }

    protected function isDesignField(FieldDefinition $field): bool
    {
        $fieldId = $field->getId();

        if (str_starts_with($fieldId, 'core.styles.') || str_starts_with($fieldId, 'styles.')) {
            return true;
        }

        return false;
    }
}
