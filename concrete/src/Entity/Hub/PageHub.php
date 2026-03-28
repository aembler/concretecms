<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\Controller\ControllerInterface;
use Concrete\Core\Application\UserInterface\Hub\Controller\PageController;
use Concrete\Core\Page\Page;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HubRepository")
 * @ORM\Table(name="PageHubs")
 */
class PageHub extends Hub
{
    /**
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    public int $cID = 0;

    public function __construct(
        ?Page $page = null,

        /**
         * @ORM\Column(type="string")
         */
        public ?string $label = null,
    ) {
        if ($page) {
            $this->cID = (int) $page->getCollectionID();
        }
    }

    public function getPage(): ?Page
    {
        if ($this->cID <= 0) {
            return null;
        }

        $page = Page::getByID($this->cID, 'ACTIVE');

        return $page && !$page->isError() ? $page : null;
    }

    public function getLabel(): string
    {
        if ($this->label === null) {
            $page = $this->getPage();
            return $page->getCollectionName();
        } else {
            return $this->label;
        }
    }

    public function setPage(Page $page = null): void
    {
        $this->cID = $page ? (int) $page->getCollectionID() : 0;
    }

    public function getController(): ControllerInterface
    {
        return new PageController($this);
    }
}
