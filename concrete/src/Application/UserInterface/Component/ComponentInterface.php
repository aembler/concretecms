<?php

namespace Concrete\Core\Application\UserInterface\Component;

interface ComponentInterface extends \JsonSerializable
{
    public function getComponent(): string;

    public function getComponentProps(): array;
}
