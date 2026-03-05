<?php

namespace Concrete\Core\Application\UserInterface\Component;

abstract class AbstractComponent implements ComponentInterface
{
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'component' => $this->getComponent(),
            'componentProps' => $this->getComponentProps(),
        ];
    }
}
