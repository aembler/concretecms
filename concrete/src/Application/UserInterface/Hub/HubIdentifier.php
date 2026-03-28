<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;

class HubIdentifier
{
    public function __construct(
        public string $identifier,
    ) {}
}
