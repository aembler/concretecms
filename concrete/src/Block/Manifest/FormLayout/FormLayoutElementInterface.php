<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

interface FormLayoutElementInterface extends \JsonSerializable
{
    public function getType(): string;
}
