<?php

namespace Concrete\Core\Application\UserInterface\Hub\Importer;

use Concrete\Core\Application\UserInterface\Hub\HubInterface;

interface ImporterInterface
{
    public function createFromImport(\SimpleXMLElement $node): HubInterface;
}
