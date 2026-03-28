<?php

namespace Concrete\Core\Backup\ContentImporter\Importer\Routine;

use Concrete\Core\Application\UserInterface\Hub\Controller\Manager;
use Concrete\Core\Entity\Hub\Hub;
use Doctrine\ORM\EntityManager;

class ImportHubsRoutine extends AbstractRoutine
{

    public function __construct(
        private Manager $controllerManager,
        private EntityManager $entityManager,
    ) {}

    public function getHandle()
    {
        return 'hubs';
    }

    public function import(\SimpleXMLElement $sx)
    {
        if (!isset($sx->hubs)) {
            return;
        }

        $repository = $this->entityManager->getRepository(Hub::class);
        $sortOrder = $repository->getNextSortOrder();

        foreach ($sx->hubs->hub as $node) {
            $type = (string) $node['type'];
            $driver = $type === 'custom' ? (string) $node['handle'] : $type;
            $controller = $this->controllerManager->driver($driver);
            $importer = $controller->getImporter();
            $hub = $importer->createFromImport($node);
            $hub->setPackage(static::getPackageObject($node['package']));
            $hub->setSortOrder($sortOrder);
            $sortOrder++;

            $this->entityManager->persist($hub);
        }
        $this->entityManager->flush();
    }
}
