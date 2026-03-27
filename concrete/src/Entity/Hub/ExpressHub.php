<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Hub\Controller\ExpressController;
use Concrete\Core\Entity\Express\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ExpressHub extends AbstractHub
{
    /**
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\Express\Entity")
     * @ORM\JoinColumn(name="exEntityID", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $entity;

    /**
     * @return \Concrete\Core\Entity\Express\Entity|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param \Concrete\Core\Entity\Express\Entity|null $entity
     */
    public function setEntity(Entity $entity = null): void
    {
        $this->entity = $entity;
    }

    public function getController(): ControllerInterface
    {
        return new ExpressController();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->entity ? (string) $this->entity->getId() : '';
    }
}
