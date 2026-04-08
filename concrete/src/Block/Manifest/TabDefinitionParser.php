<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Exception\MalformedManifestException;
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
                $children = [];
                $seenFieldRefs = [];
                foreach ($tabNode->children() as $child) {
                    if (!($child instanceof SimpleXMLElement)) {
                        continue;
                    }

                    if ($child->getName() !== 'fieldref') {
                        throw new MalformedManifestException(sprintf('Unsupported tab child element <%s>.', $child->getName()));
                    }

                    $fieldId = $this->getRequiredAttribute($child, 'field', '<fieldref>');
                    if (isset($seenFieldRefs[$fieldId])) {
                        throw new MalformedManifestException(sprintf('Field "%s" may only appear once in tab "%s".', $fieldId, $tabId));
                    }

                    $seenFieldRefs[$fieldId] = true;
                    $children[] = new FieldReference($fieldId);
                }

                $result[$tabId] = new Tab($tabId, $name, $children);
            }
        }

        return $result;
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
