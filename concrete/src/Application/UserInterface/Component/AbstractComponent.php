<?php

namespace Concrete\Core\Application\UserInterface\Component;

abstract class AbstractComponent implements ComponentInterface
{
    public function jsonSerialize(): mixed
    {
        return [
            'component' => $this->getComponent(),
            'componentProps' => $this->getComponentProps(),
        ];
    }
}
