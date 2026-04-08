<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

final class TabReference implements FormLayoutElementInterface
{
    /**
     * @var string
     */
    protected $tabId;

    public function __construct(string $tabId)
    {
        $this->tabId = $tabId;
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
     * @return array{type: string, tab: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'tab' => $this->tabId,
        ];
    }
}
