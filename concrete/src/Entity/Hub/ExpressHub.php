<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Hub\Controller\ExpressController;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;
use Concrete\Core\Application\UserInterface\Icon\InlineSvgIcon;
use Concrete\Core\Entity\Express\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HubRepository")
 * @ORM\Table(name="ExpressHubs")
 */
class ExpressHub extends Hub
{
    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Core\Entity\Express\Entity")
     */
    public ?Entity $entity = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $icon = null;

    public function __construct(
        string $handle,
        Entity $entity,
        ?string $icon = null,
    ) {
        $this->handle = $handle;
        $this->entity = $entity;
        $this->icon = $icon;
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

    public function getLabel(): string
    {
        /** @todo - have a proper plural name */
        return camelcase($this->entity->getPluralHandle());
    }

    public function getHomePageUrl(): string
    {
        return '#';
    }

    public function getIcon(): IconInterface
    {
        return new InlineSvgIcon((string) $this->icon);
    }
}
