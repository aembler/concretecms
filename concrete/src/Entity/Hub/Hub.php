<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\HubIdentifier;
use Concrete\Core\Application\UserInterface\Hub\HubInterface;
use Concrete\Core\Entity\PackageTrait;
use Concrete\Core\Package\PackageService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HubRepository")
 * @ORM\Table(name="Hubs")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "custom" = "CustomHub",
 *     "page" = "PageHub",
 *     "express" = "ExpressHub"
 * })
 */
abstract class Hub implements HubInterface
{
    use PackageTrait;

    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $handle;

    /**
     * @ORM\Column(type="integer")
     */
    public $sortOrder = 0;

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

    public function inHub(?HubIdentifier $hub): bool
    {
        return $hub->identifier === $this->handle;
    }
}
