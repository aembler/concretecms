<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Entity\Hub\AbstractHub;
use Doctrine\ORM\EntityManager;

class CreateHubCommandHandler
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateHubCommand $command)
    {
        $hub = $command->getHub();
        $existing = $this->findExistingHubByIdentifier($hub->getIdentifier());
        if ($existing) {
            return $existing;
        }

        $hub->setSortOrder($this->getNextSortOrder());
        $this->entityManager->persist($hub);
        $this->entityManager->flush();

        return $hub;
    }

    /**
     * @param string $identifier
     *
     * @return \Concrete\Core\Entity\Hub\AbstractHub|null
     */
    protected function findExistingHubByIdentifier($identifier)
    {
        $hubs = $this->entityManager->getRepository(AbstractHub::class)->findAll();
        foreach ($hubs as $hub) {
            if ($hub->getIdentifier() === $identifier) {
                return $hub;
            }
        }
    }

    /**
     * @return int
     */
    protected function getNextSortOrder()
    {
        $sortOrder = $this->entityManager->createQuery(
            'select max(h.sortOrder) from ' . AbstractHub::class . ' h'
        )->getSingleScalarResult();

        return ((int) $sortOrder) + 1;
    }
}
