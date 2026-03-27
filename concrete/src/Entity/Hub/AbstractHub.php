<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\HubInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Hubs")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "hub" = "Hub",
 *     "express" = "ExpressHub"
 * })
 */
abstract class AbstractHub implements HubInterface
{
    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sortOrder = 0;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder): void
    {
        $this->sortOrder = (int) $sortOrder;
    }

    /**
     * @return string
     */
    abstract public function getIdentifier();
}
