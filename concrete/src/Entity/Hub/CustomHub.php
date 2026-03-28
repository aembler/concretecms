<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Hub\Controller\Manager;
use Concrete\Core\Application\UserInterface\Hub\HubInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HubRepository")
 * @ORM\Table(name="CustomHubs")
 */
class CustomHub extends Hub
{
    public function __construct(
        /**
         * @ORM\Column(type="string", length=255)
         */
        public string $handle = '',
    ) {
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param string $handle
     */
    public function setHandle($handle): void
    {
        $this->handle = (string) $handle;
    }

    public function getController(): ControllerInterface
    {
        return app(Manager::class)->driver($this->getHandle());
    }
}
