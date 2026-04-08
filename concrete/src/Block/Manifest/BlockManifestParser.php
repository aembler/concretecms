<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Error\ManifestError;
use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Block\Manifest\FormLayout\ContainerElement;
use Concrete\Core\Block\Manifest\FormLayout\Fieldset;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use Concrete\Core\Block\Manifest\FormLayout\TabReference;
use SimpleXMLElement;

final class BlockManifestParser
{
    /**
     * @var \Concrete\Core\Block\Manifest\Field\FieldManager
     */
    protected $fieldDefinitionParser;

    /**
     * @var \Concrete\Core\Block\Manifest\TabDefinitionParser
     */
    protected $tabDefinitionParser;

    /**
     * @var \Concrete\Core\Block\Manifest\GlobalFieldRegistry
     */
    protected $globalFieldRegistry;

    public function __construct(FieldDefinitionParser $fieldDefinitionParser, TabDefinitionParser $tabDefinitionParser, GlobalFieldRegistry $globalFieldRegistry)
    {
        $this->fieldDefinitionParser = $fieldDefinitionParser;
        $this->tabDefinitionParser = $tabDefinitionParser;
        $this->globalFieldRegistry = $globalFieldRegistry;
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
        $globalFields = $this->globalFieldRegistry->getFields();
        $globalTabs = $this->globalFieldRegistry->getTabs();
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
        $localFields = $this->parseFields($blockType, $errors);
        $fields = $this->mergeFields($localFields, $globalFields);
        $localTabs = $this->parseTabs($blockType);
        $tabs = $this->mergeTabs($localTabs, $globalTabs);
        $seenFieldRefs = [];
        $layout = $this->parseFormLayout($blockType, $fields, $tabs, $seenFieldRefs, $errors);

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
        return $this->fieldDefinitionParser->parseFieldGroups($blockType, $errors);
    }

    /**
     * @return array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab>
     */
    protected function parseTabs(SimpleXMLElement $blockType): array
    {
        return $this->tabDefinitionParser->parseTabGroups($blockType);
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab> $tabs
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     *
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected function parseFormLayout(SimpleXMLElement $blockType, array $fields, array $tabs, array &$seenFieldRefs, array &$errors): array
    {
        $formLayoutNodes = $blockType->xpath('./formlayout');
        if (!is_array($formLayoutNodes) || !isset($formLayoutNodes[0]) || !($formLayoutNodes[0] instanceof SimpleXMLElement)) {
            return [];
        }

        return $this->parseLayoutChildren($formLayoutNodes[0], $fields, $tabs, $seenFieldRefs, $errors);
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab> $tabs
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     *
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected function parseLayoutChildren(SimpleXMLElement $parent, array $fields, array $tabs, array &$seenFieldRefs, array &$errors): array
    {
        $elements = [];
        foreach ($parent->children() as $child) {
            if (!($child instanceof SimpleXMLElement)) {
                continue;
            }

            $elements[] = $this->parseLayoutElement($child, $fields, $tabs, $seenFieldRefs, $errors);
        }

        return $elements;
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab> $tabs
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     */
    protected function parseLayoutElement(SimpleXMLElement $element, array $fields, array $tabs, array &$seenFieldRefs, array &$errors): FormLayoutElementInterface
    {
        switch ($element->getName()) {
            case 'fieldref':
                $fieldId = $this->getRequiredAttribute($element, 'field', '<fieldref>');
                if (isset($seenFieldRefs[$fieldId])) {
                    throw new MalformedManifestException(sprintf('Field "%s" may only appear once in the form layout.', $fieldId));
                }
                $seenFieldRefs[$fieldId] = true;

                if (!isset($fields[$fieldId])) {
                    $errors[] = new ManifestError(
                        'unknown_field_reference',
                        sprintf('Unknown field reference "%s" in form layout.', $fieldId),
                        [
                            'fieldId' => $fieldId,
                        ]
                    );

                    return new FieldReference($fieldId);
                }

                return new FieldReference($fieldId);

            case 'tab':
                return $this->parseContainerElement($element, Tab::class, $fields, $tabs, $seenFieldRefs, $errors);

            case 'fieldset':
                return $this->parseFieldsetElement($element, $fields, $seenFieldRefs, $errors);

            case 'tabref':
                $tabId = $this->getRequiredAttribute($element, 'tab', '<tabref>');
                $excludedFieldIds = $this->parseTabReferenceExcludedFields($element);
                if (!isset($tabs[$tabId])) {
                    $errors[] = new ManifestError(
                        'unknown_tab_reference',
                        sprintf('Unknown tab reference "%s" in form layout.', $tabId),
                        [
                            'tabId' => $tabId,
                        ]
                    );

                    return new TabReference($tabId, $excludedFieldIds);
                }

                return $this->resolveTab($tabs[$tabId], $fields, $seenFieldRefs, $errors, $excludedFieldIds);
        }

        throw new MalformedManifestException(sprintf('Unsupported form layout element <%s>.', $element->getName()));
    }

    /**
     * @param class-string<\Concrete\Core\Block\Manifest\FormLayout\ContainerElement> $className
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab> $tabs
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     */
    protected function parseContainerElement(SimpleXMLElement $element, string $className, array $fields, array $tabs, array &$seenFieldRefs, array &$errors): ContainerElement
    {
        $id = $this->getRequiredAttribute($element, 'id', sprintf('<%s>', $element->getName()));
        $name = trim((string) ($element['name'] ?? $element['label'] ?? $id));
        $children = $this->parseLayoutChildren($element, $fields, $tabs, $seenFieldRefs, $errors);

        return new $className($id, $name, $children);
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     */
    protected function parseFieldsetElement(SimpleXMLElement $element, array $fields, array &$seenFieldRefs, array &$errors): Fieldset
    {
        $legend = trim((string) ($element['legend'] ?? ''));
        $children = [];
        foreach ($element->children() as $child) {
            if (!($child instanceof SimpleXMLElement)) {
                continue;
            }

            if ($child->getName() !== 'fieldref') {
                throw new MalformedManifestException(sprintf('Unsupported fieldset child element <%s>.', $child->getName()));
            }

            $children[] = $this->parseLayoutElement($child, $fields, [], $seenFieldRefs, $errors);
        }

        return new Fieldset($legend, $legend, $children);
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $localFields
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $globalFields
     *
     * @return array<string, \Concrete\Core\Block\Manifest\FieldDefinition>
     */
    protected function mergeFields(array $localFields, array $globalFields): array
    {
        $merged = $globalFields;
        foreach ($localFields as $fieldId => $fieldDefinition) {
            if (isset($merged[$fieldId])) {
                throw new MalformedManifestException(sprintf('Field id "%s" collides with a globally registered field.', $fieldId));
            }

            $merged[$fieldId] = $fieldDefinition;
        }

        return $merged;
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab> $localTabs
     * @param array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab> $globalTabs
     *
     * @return array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab>
     */
    protected function mergeTabs(array $localTabs, array $globalTabs): array
    {
        $merged = $globalTabs;
        foreach ($localTabs as $tabId => $tabDefinition) {
            if (isset($merged[$tabId])) {
                throw new MalformedManifestException(sprintf('Tab id "%s" collides with a globally registered tab.', $tabId));
            }

            $merged[$tabId] = $tabDefinition;
        }

        return $merged;
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     */
    protected function resolveTab(Tab $tab, array $fields, array &$seenFieldRefs, array &$errors, array $excludedFieldIds = []): Tab
    {
        $children = [];
        $excludedFieldMap = array_fill_keys($excludedFieldIds, true);
        foreach ($tab->getChildren() as $child) {
            if ($child instanceof FieldReference) {
                if (!isset($excludedFieldMap[$child->getFieldId()])) {
                    $children[] = $this->resolveFieldReference($child, $fields, $seenFieldRefs, $errors);
                }
                continue;
            }

            if ($child instanceof Fieldset) {
                $fieldsetChildren = [];
                foreach ($child->getChildren() as $fieldsetChild) {
                    if (!($fieldsetChild instanceof FieldReference)) {
                        continue;
                    }

                    if (isset($excludedFieldMap[$fieldsetChild->getFieldId()])) {
                        continue;
                    }

                    $fieldsetChildren[] = $this->resolveFieldReference($fieldsetChild, $fields, $seenFieldRefs, $errors);
                }

                if ($fieldsetChildren !== []) {
                    $children[] = new Fieldset($child->getId(), $child->getName(), $fieldsetChildren);
                }
            }
        }

        return new Tab($tab->getId(), $tab->getName(), $children);
    }

    /**
     * @return list<string>
     */
    protected function parseTabReferenceExcludedFields(SimpleXMLElement $element): array
    {
        $excludedFieldIds = [];
        foreach ($element->children() as $child) {
            if (!($child instanceof SimpleXMLElement)) {
                continue;
            }

            if ($child->getName() !== 'excludefield') {
                throw new MalformedManifestException(sprintf('Unsupported tabref child element <%s>.', $child->getName()));
            }

            $fieldId = $this->getRequiredAttribute($child, 'field', '<excludefield>');
            if (in_array($fieldId, $excludedFieldIds, true)) {
                throw new MalformedManifestException(sprintf('Field "%s" may only be excluded once from tab reference.', $fieldId));
            }

            $excludedFieldIds[] = $fieldId;
        }

        return $excludedFieldIds;
    }

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, bool> $seenFieldRefs
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     */
    protected function resolveFieldReference(FieldReference $fieldReference, array $fields, array &$seenFieldRefs, array &$errors): FieldReference
    {
        $fieldId = $fieldReference->getFieldId();
        if (isset($seenFieldRefs[$fieldId])) {
            throw new MalformedManifestException(sprintf('Field "%s" may only appear once in the form layout.', $fieldId));
        }
        $seenFieldRefs[$fieldId] = true;

        if (!isset($fields[$fieldId])) {
            $errors[] = new ManifestError(
                'unknown_field_reference',
                sprintf('Unknown field reference "%s" in form layout.', $fieldId),
                [
                    'fieldId' => $fieldId,
                ]
            );
        }

        return new FieldReference($fieldId);
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
