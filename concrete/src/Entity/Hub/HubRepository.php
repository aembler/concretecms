<?php

namespace Concrete\Core\Entity\Hub;

use Doctrine\ORM\EntityRepository;

class HubRepository extends EntityRepository
{
    public function getNextSortOrder(): int
    {
        $sortOrder = $this->createQueryBuilder('h')
            ->select('MAX(h.sortOrder)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($sortOrder === null) {
            return 0;
        }

        return ((int) $sortOrder) + 1;
    }
}
