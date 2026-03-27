<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\Controller;
use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Hub extends AbstractHub
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $identifier;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return (string) $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = (string) $identifier;
    }

    public function getController(): ControllerInterface
    {
        return new Controller();
    }
}
