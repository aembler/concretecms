<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Hub\Controller\Manager;
use Concrete\Core\Application\UserInterface\Hub\Controller\PageController;
use Concrete\Core\Application\UserInterface\Hub\HubInterface;
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
     * @return string
     */
    public function getHandle()
    {
        return $this->getIdentifier();
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = (string) $identifier;
    }

    /**
     * @param string $handle
     */
    public function setHandle($handle): void
    {
        $this->setIdentifier($handle);
    }

    public static function fromXml(\SimpleXMLElement $node): HubInterface
    {
        $hub = new static();
        $hub->setHandle((string) $node['handle']);
        $hub->setPackage(static::getPackageObject($node['package']));

        return $hub;
    }

    public function getController(): ControllerInterface
    {
        return app(Manager::class)->driver($this->getHandle());
    }
}
