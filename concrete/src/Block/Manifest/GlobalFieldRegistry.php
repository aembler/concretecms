<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Cache\Cache;
use SimpleXMLElement;

final class GlobalFieldRegistry
{
    /**
     * @var \Concrete\Core\Block\Manifest\FieldDefinitionParser
     */
    protected $fieldDefinitionParser;

    /**
     * @var \Concrete\Core\Cache\Cache|null
     */
    protected $cache;

    /**
     * @var list<string>
     */
    protected $sources = [];

    public function __construct(FieldDefinitionParser $fieldDefinitionParser, ?Cache $cache = null)
    {
        $this->fieldDefinitionParser = $fieldDefinitionParser;
        $this->cache = $cache;
    }

    public function addSource(string $source): void
    {
        if (!in_array($source, $this->sources, true)) {
            $this->sources[] = $source;
        }
    }

    /**
     * @return array<string, \Concrete\Core\Block\Manifest\FieldDefinition>
     */
    public function getFields(): array
    {
        $payload = $this->load();

        return $payload['fields'];
    }

    /**
     * @return list<\Concrete\Core\Block\Manifest\Error\ManifestError>
     */
    public function getErrors(): array
    {
        $payload = $this->load();

        return $payload['errors'];
    }

    public function has(string $fieldId): bool
    {
        return isset($this->getFields()[$fieldId]);
    }

    public function get(string $fieldId): ?FieldDefinition
    {
        return $this->getFields()[$fieldId] ?? null;
    }

    /**
     * @return array{
     *   fields: array<string, \Concrete\Core\Block\Manifest\FieldDefinition>,
     *   errors: list<\Concrete\Core\Block\Manifest\Error\ManifestError>
     * }
     */
    protected function load(): array
    {
        if ($this->cache !== null) {
            $item = $this->cache->getItem($this->getCacheKey());
            $payload = $item->get();
            if (is_array($payload) && isset($payload['fields'], $payload['errors'])) {
                return $payload;
            }

            $payload = $this->parseSources();
            $item->set($payload);
            $this->cache->save($item);

            return $payload;
        }

        return $this->parseSources();
    }

    /**
     * @return array{
     *   fields: array<string, \Concrete\Core\Block\Manifest\FieldDefinition>,
     *   errors: list<\Concrete\Core\Block\Manifest\Error\ManifestError>
     * }
     */
    protected function parseSources(): array
    {
        $fields = [];
        $errors = [];

        foreach ($this->sources as $source) {
            if (!is_file($source)) {
                throw new MalformedManifestException(sprintf('Global manifest source not found: %s', $source));
            }

            $contents = file_get_contents($source);
            if ($contents === false) {
                throw new MalformedManifestException(sprintf('Unable to read global manifest source: %s', $source));
            }

            $element = $this->loadXml($contents, $source);
            if ($element->getName() !== 'concrete-bdf') {
                throw new MalformedManifestException(sprintf('The global manifest root element must be <concrete-bdf>. (%s)', $source));
            }

            $parsedFields = $this->fieldDefinitionParser->parseFieldGroups($element, $errors);
            foreach ($parsedFields as $fieldId => $fieldDefinition) {
                if (isset($fields[$fieldId])) {
                    throw new MalformedManifestException(sprintf('Duplicate global field id "%s" found while loading %s.', $fieldId, $source));
                }

                $fields[$fieldId] = $fieldDefinition;
            }
        }

        return [
            'fields' => $fields,
            'errors' => $errors,
        ];
    }

    protected function getCacheKey(): string
    {
        $parts = [];
        foreach ($this->sources as $source) {
            $parts[] = $source . '|' . (is_file($source) ? (string) filemtime($source) : 'missing');
        }

        return 'manifest/global_fields/' . md5(implode(';', $parts));
    }

    protected function loadXml(string $xml, string $source = ''): SimpleXMLElement
    {
        $internalErrors = libxml_use_internal_errors(true);
        $element = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        if (!($element instanceof SimpleXMLElement)) {
            $message = 'Failed to parse global manifest XML.';
            if ($errors !== []) {
                $message = trim($errors[0]->message);
            }
            if ($source !== '') {
                $message = sprintf('%s (%s)', $message, $source);
            }

            throw new MalformedManifestException($message);
        }

        return $element;
    }
}
