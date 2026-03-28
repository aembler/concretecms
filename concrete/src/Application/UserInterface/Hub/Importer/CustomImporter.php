<?php

namespace Concrete\Core\Application\UserInterface\Hub\Importer;

use Concrete\Core\Application\UserInterface\Hub\HubInterface;
use Concrete\Core\Entity\Hub\CustomHub;

class CustomImporter implements ImporterInterface
{

    public function __construct(
        public string $handle,
    ) {}
    public function createFromImport(\SimpleXMLElement $node): HubInterface
    {
        return new CustomHub($this->handle);
    }
}
