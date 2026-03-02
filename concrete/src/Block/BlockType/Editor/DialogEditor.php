<?php

namespace Concrete\Core\Block\BlockType\Editor;

class DialogEditor extends AbstractEditor
{
    public function __construct(
        protected string $dialogTitle = '',
        protected string|int $dialogWidth = 'auto',
        protected string|int $dialogHeight = 'auto'
    ) {
    }

    public function getComponentKey(): string
    {
        return 'DialogEditor';
    }

    public function getComponentProps(): array
    {
        return [
            'dialogTitle' => $this->dialogTitle,
            'dialogWidth' => $this->dialogWidth,
            'dialogHeight' => $this->dialogHeight,
        ];
    }
}
