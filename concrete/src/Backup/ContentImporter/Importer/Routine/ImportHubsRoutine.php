<?php

namespace Concrete\Core\Backup\ContentImporter\Importer\Routine;

use Concrete\Core\Entity\Hub\AbstractHub;
use Concrete\Core\Entity\Hub\Hub;
use Concrete\Core\Application\UserInterface\Hub\CreateHubCommand;

class ImportHubsRoutine extends AbstractRoutine
{
    public function getHandle()
    {
        return 'hubs';
    }

    public function import(\SimpleXMLElement $sx)
    {
        if (!isset($sx->hubs)) {
            return;
        }

        $app = app();
        $em = \Database::connection()->getEntityManager();
        foreach ($sx->hubs->hub as $node) {
            $identifier = (string) $node['handle'];
            $existing = $this->findExistingHubByIdentifier($em, $identifier);
            if ($existing) {
                continue;
            }

            $hub = Hub::fromXml($node);
            $app->executeCommand(new CreateHubCommand($hub));
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param string $identifier
     *
     * @return \Concrete\Core\Entity\Hub\AbstractHub|null
     */
    protected function findExistingHubByIdentifier($entityManager, $identifier)
    {
        $hubs = $entityManager->getRepository(AbstractHub::class)->findAll();
        foreach ($hubs as $hub) {
            if ($hub->getIdentifier() === $identifier) {
                return $hub;
            }
        }
    }
}
