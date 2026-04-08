<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

final class Tab extends ContainerElement
{
    public function getType(): string
    {
        return 'tab';
    }
}
