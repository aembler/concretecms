<?php

namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Navigation\Navigation;
use Concrete\Core\Navigation\NavigationInterface;
use Concrete\Core\Url\Url;
use Concrete\Core\Url\UrlInterface;

class ExpressController implements ControllerInterface
{
    /**
     * @var \Concrete\Core\Entity\Express\Entity|null
     */
    protected $entity;

    public function __construct(Entity $entity = null)
    {
        $this->entity = $entity;
    }

    public function getNavigation(): NavigationInterface
    {
        return new Navigation();
    }

    public function getMenuTitle(): string
    {
        return $this->entity ? (string) $this->entity->getName() : '';
    }

    public function getHomePageUrl(): UrlInterface
    {
        return Url::createFromUrl('https://www.yahoo.com');
    }
}
