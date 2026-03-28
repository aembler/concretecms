<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Hub\Controller\ExpressController;
use Concrete\Core\Entity\Express\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HubRepository")
 * @ORM\Table(name="ExpressHubs")
 */
class ExpressHub extends Hub
{
    public function __construct(
        /**
         * @ORM\ManyToOne(targetEntity="Concrete\Core\Entity\Express\Entity")
         */
        public ?Entity $entity = null,
    ) {
    }

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
        return new ExpressController($this->entity);
    }
}
