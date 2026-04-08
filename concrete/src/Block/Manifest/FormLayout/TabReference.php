<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

final class TabReference implements FormLayoutElementInterface
{
    /**
     * @var string
     */
    protected $tabId;

    /**
     * @var list<string>
     */
    protected $excludedFieldIds;

    /**
     * @param list<string> $excludedFieldIds
     */
    public function __construct(string $tabId, array $excludedFieldIds = [])
    {
        $this->tabId = $tabId;
        $this->excludedFieldIds = array_values($excludedFieldIds);
    }

    public function getTabId(): string
    {
        return $this->tabId;
    }

    public function getType(): string
    {
        return 'tabref';
    }

    /**
     * @return list<string>
     */
    public function getExcludedFieldIds(): array
    {
        return $this->excludedFieldIds;
    }

    /**
     * @return array{type: string, tab: string, excludeFields: list<string>}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'tab' => $this->tabId,
            'excludeFields' => $this->excludedFieldIds,
        ];
    }
}
