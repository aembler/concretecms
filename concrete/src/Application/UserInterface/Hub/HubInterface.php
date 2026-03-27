<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;

interface HubInterface extends \JsonSerializable
{
    public function getController(): ControllerInterface;

    public static function fromXml(\SimpleXMLElement $node): HubInterface;
}
