<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\View;

final class ColorFieldViewValue extends FieldViewValue
{
    public function getHex(): string
    {
        return (string) $this->getValue();
    }
}
