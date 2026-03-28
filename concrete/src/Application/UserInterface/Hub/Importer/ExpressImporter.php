<?php

namespace Concrete\Core\Application\UserInterface\Hub\Importer;

use Concrete\Core\Application\UserInterface\Hub\HubInterface;
use Concrete\Core\Entity\Hub\ExpressHub;
use Concrete\Core\Express\ObjectManager;

class ExpressImporter implements ImporterInterface
{
    public function __construct(
        protected ObjectManager $objectManager
    ) {
    }

    public function createFromImport(\SimpleXMLElement $node): HubInterface
    {
        $entity = null;
        if (isset($node->express)) {
            $expressNode = $node->express;
            $handle = trim((string) $expressNode['handle']);
            if ($handle !== '') {
                $entity = $this->objectManager->getObjectByHandle($handle);
            }
        }

        if (!$entity) {
            throw new \RuntimeException('Unable to resolve the express entity referenced by this express hub import node.');
        }

        $hub = new ExpressHub($entity);
        return $hub;
    }
}
