<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
use Concrete\Core\Block\Manifest\FormLayout\Fieldset;
use Concrete\Core\Block\Manifest\FormLayout\FieldReference;
use Concrete\Core\Block\Manifest\FormLayout\Tab;
use SimpleXMLElement;

final class TabDefinitionParser
{
    /**
     * @return array<string, \Concrete\Core\Block\Manifest\FormLayout\Tab>
     */
    public function parseTabGroups(SimpleXMLElement $parent): array
    {
        $result = [];
        $tabGroups = $parent->xpath('./tabs');
        if (!is_array($tabGroups)) {
            return $result;
        }

        foreach ($tabGroups as $tabGroup) {
            if (!($tabGroup instanceof SimpleXMLElement)) {
                continue;
            }

            foreach ($tabGroup->xpath('./tab') ?: [] as $tabNode) {
                if (!($tabNode instanceof SimpleXMLElement)) {
                    continue;
                }

                $tabId = $this->getRequiredAttribute($tabNode, 'id', '<tab>');
                if (isset($result[$tabId])) {
                    throw new MalformedManifestException(sprintf('Duplicate tab id "%s" found in manifest.', $tabId));
                }

                $name = trim((string) ($tabNode['name'] ?? $tabNode['label'] ?? $tabId));
                $seenFieldRefs = [];
                $children = $this->parseTabChildren($tabNode, $tabId, $seenFieldRefs);

                $result[$tabId] = new Tab($tabId, $name, $children);
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool> $seenFieldRefs
     *
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected function parseTabChildren(SimpleXMLElement $tabNode, string $tabId, array &$seenFieldRefs): array
    {
        $children = [];
        foreach ($tabNode->children() as $child) {
            if (!($child instanceof SimpleXMLElement)) {
                continue;
            }

            switch ($child->getName()) {
                case 'fieldref':
                    $children[] = $this->parseFieldReference($child, $tabId, $seenFieldRefs);
                    break;

                case 'fieldset':
                    $children[] = $this->parseFieldset($child, $tabId, $seenFieldRefs);
                    break;

                default:
                    throw new MalformedManifestException(sprintf('Unsupported tab child element <%s>.', $child->getName()));
            }
        }

        return $children;
    }

    /**
     * @param array<string, bool> $seenFieldRefs
     */
    protected function parseFieldReference(SimpleXMLElement $fieldNode, string $tabId, array &$seenFieldRefs): FieldReference
    {
        $fieldId = $this->getRequiredAttribute($fieldNode, 'field', '<fieldref>');
        if (isset($seenFieldRefs[$fieldId])) {
            throw new MalformedManifestException(sprintf('Field "%s" may only appear once in tab "%s".', $fieldId, $tabId));
        }

        $seenFieldRefs[$fieldId] = true;

        return new FieldReference($fieldId);
    }

    /**
     * @param array<string, bool> $seenFieldRefs
     */
    protected function parseFieldset(SimpleXMLElement $fieldsetNode, string $tabId, array &$seenFieldRefs): Fieldset
    {
        $legend = trim((string) ($fieldsetNode['legend'] ?? ''));
        $children = [];
        foreach ($fieldsetNode->children() as $child) {
            if (!($child instanceof SimpleXMLElement)) {
                continue;
            }

            if ($child->getName() !== 'fieldref') {
                throw new MalformedManifestException(sprintf('Unsupported fieldset child element <%s>.', $child->getName()));
            }

            $children[] = $this->parseFieldReference($child, $tabId, $seenFieldRefs);
        }

        return new Fieldset($legend, $legend, $children);
    }

    protected function getRequiredAttribute(SimpleXMLElement $element, string $attributeName, string $elementLabel): string
    {
        $value = trim((string) ($element[$attributeName] ?? ''));
        if ($value === '') {
            throw new MalformedManifestException(sprintf('Missing required attribute "%s" on %s.', $attributeName, $elementLabel));
        }

        return $value;
    }
}
