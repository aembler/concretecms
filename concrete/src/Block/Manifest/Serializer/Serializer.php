<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Serializer;

use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\Field\EnvelopeSectionResolver;
use Concrete\Core\Block\Manifest\FieldDefinition;
use JsonException;
use Psr\Log\LoggerInterface;

class Serializer
{
    public function __construct(
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{version: int, fields: array<string, mixed>, meta: array<string, mixed>}
     */
    public static function emptyEnvelope(): array
    {
        return [
            'version' => 1,
            'fields' => [],
            'meta' => [],
        ];
    }

    public function serializeFromRequest(BlockManifest $manifest, array $requestArgs): string
    {
        $payload = self::emptyEnvelope();

        foreach ($manifest->getFields() as $field) {

            $fieldType = $field->getFieldType();

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
        return EnvelopeSectionResolver::resolve($field);
    }
}
