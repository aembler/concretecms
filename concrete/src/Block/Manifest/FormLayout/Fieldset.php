<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

final class Fieldset extends ContainerElement
{
    public function getType(): string
    {
        return 'fieldset';
    }

    public function getLegend(): string
    {
        return $this->getName();
    }
}
