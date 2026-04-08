<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Error\ManifestError;
use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Block\Manifest\Field\FieldManager;
use Concrete\Core\Block\Manifest\FormLayout\ContainerElement;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use SimpleXMLElement;

final class BlockManifestParser
{
    /**
     * @var \Concrete\Core\Block\Manifest\Field\FieldManager
     */
    protected $fieldManager;

    public function __construct(FieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

    public function parseFile(string $filename): BlockManifest
    {
        if (!is_file($filename)) {
            throw new MalformedManifestException(sprintf('Manifest file not found: %s', $filename));
        }

        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new MalformedManifestException(sprintf('Unable to read manifest file: %s', $filename));
        }

        return $this->parseString($contents, $filename);
    }

    public function parseString(string $xml, string $source = ''): BlockManifest
    {
        $element = $this->loadXml($xml, $source);
        if ($element->getName() !== 'concrete-bdf') {
            throw new MalformedManifestException('The manifest root element must be <concrete-bdf>.');
        }

        $schemaVersion = $this->getRequiredAttribute($element, 'version', '<concrete-bdf>');
        $blockTypes = $element->xpath('./blocktype');
        if (!is_array($blockTypes) || count($blockTypes) !== 1 || !($blockTypes[0] instanceof SimpleXMLElement)) {
            throw new MalformedManifestException('A manifest must contain exactly one <blocktype> element.');
        }

        $blockType = $blockTypes[0];
        $handle = $this->getRequiredAttribute($blockType, 'handle', '<blocktype>');
        $name = $this->getRequiredAttribute($blockType, 'name', '<blocktype>');
        $description = trim((string) ($blockType['description'] ?? ''));
        $package = trim((string) ($blockType['package'] ?? ''));
        $icon = $this->extractIconMarkup($blockType);

        $errors = [];
        $fields = $this->parseFields($blockType, $errors);
        $seenFieldRefs = [];
        $layout = $this->parseFormLayout($blockType, $fields, $seenFieldRefs);

        return new BlockManifest(
            $handle,
            $name,
            $description,
            $package,
            $schemaVersion,
            $icon,
            $fields,
            $layout,
            $errors
        );
    }

    /**
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     *
     * @return array<string, \Concrete\Core\Block\Manifest\FieldDefinition>
     */
    protected function parseFields(SimpleXMLElement $blockType, array &$errors): array
    {
        $result = [];
        $fieldNodes = $blockType->xpath('./fields/field');
        if (!is_array($fieldNodes)) {
            return $result;
        }

        foreach ($fieldNodes as $fieldNode) {
            if (!($fieldNode instanceof SimpleXMLElement)) {
                continue;
            }

            $fieldId = $this->getRequiredAttribute($fieldNode, 'id', '<field>');
            if (isset($result[$fieldId])) {
                throw new MalformedManifestException(sprintf('Duplicate field id "%s" found in manifest.', $fieldId));
            }

            $fieldTypeHandle = $this->getRequiredAttribute($fieldNode, 'type', sprintf('<field id="%s">', $fieldId));
            $label = trim((string) ($fieldNode['label'] ?? $fieldId));
            $definition = $this->collectAttributes($fieldNode);
            $fieldType = $this->fieldManager->get($fieldTypeHandle);
            if ($fieldType !== null) {
                $definition = $fieldType->normalizeDefinition($definition);
            } else {
                $errors[] = new ManifestError(
                    'unknown_field_type',
                    sprintf('Unknown field type "%s" for field "%s".', $fieldTypeHandle, $fieldId),
                    [
                        'fieldId' => $fieldId,
                        'fieldType' => $fieldTypeHandle,
                    ]
                );
            }

            $result[$fieldId] = new FieldDefinition($fieldId, $fieldTypeHandle, $label, $definition, $fieldType);
        }

        return $result;
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     *
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected function parseFormLayout(SimpleXMLElement $blockType, array $fields, array &$seenFieldRefs): array
    {
        $formLayoutNodes = $blockType->xpath('./formlayout');
        if (!is_array($formLayoutNodes) || !isset($formLayoutNodes[0]) || !($formLayoutNodes[0] instanceof SimpleXMLElement)) {
            return [];
        }

        return $this->parseLayoutChildren($formLayoutNodes[0], $fields, $seenFieldRefs);
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     *
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected function parseLayoutChildren(SimpleXMLElement $parent, array $fields, array &$seenFieldRefs): array
    {
        $elements = [];
        foreach ($parent->children() as $child) {
            if (!($child instanceof SimpleXMLElement)) {
                continue;
            }

            $elements[] = $this->parseLayoutElement($child, $fields, $seenFieldRefs);
        }

        return $elements;
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     */
    protected function parseLayoutElement(SimpleXMLElement $element, array $fields, array &$seenFieldRefs): FormLayoutElementInterface
    {
        switch ($element->getName()) {
            case 'field':
                $fieldId = $this->getRequiredAttribute($element, 'id', '<field>');
                if (!isset($fields[$fieldId])) {
                    throw new MalformedManifestException(sprintf('Layout references unknown field "%s".', $fieldId));
                }
                if (isset($seenFieldRefs[$fieldId])) {
                    throw new MalformedManifestException(sprintf('Field "%s" may only appear once in the form layout.', $fieldId));
                }
                $seenFieldRefs[$fieldId] = true;

                return new FieldReference($fieldId);

            case 'tab':
                return $this->parseContainerElement($element, Tab::class, $fields, $seenFieldRefs);
        }

        throw new MalformedManifestException(sprintf('Unsupported form layout element <%s>.', $element->getName()));
    }

    /**
     * @param class-string<\Concrete\Core\Block\Manifest\FormLayout\ContainerElement> $className
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     */
    protected function parseContainerElement(SimpleXMLElement $element, string $className, array $fields, array &$seenFieldRefs): ContainerElement
    {
        $id = $this->getRequiredAttribute($element, 'id', sprintf('<%s>', $element->getName()));
        $name = trim((string) ($element['name'] ?? $element['label'] ?? $id));
        $children = $this->parseLayoutChildren($element, $fields, $seenFieldRefs);

        return new $className($id, $name, $children);
    }

    protected function loadXml(string $xml, string $source = ''): SimpleXMLElement
    {
        $internalErrors = libxml_use_internal_errors(true);
        $element = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        if (!($element instanceof SimpleXMLElement)) {
            $message = 'Failed to parse manifest XML.';
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

    protected function extractIconMarkup(SimpleXMLElement $blockType): string
    {
        $iconNodes = $blockType->xpath('./icon');
        if (!is_array($iconNodes) || !isset($iconNodes[0]) || !($iconNodes[0] instanceof SimpleXMLElement)) {
            return '';
        }

        foreach ($iconNodes[0]->children() as $child) {
            $domNode = dom_import_simplexml($child);
            if ($domNode !== false && $domNode->ownerDocument !== null) {
                return (string) $domNode->ownerDocument->saveXML($domNode);
            }
        }

        return '';
    }
}
