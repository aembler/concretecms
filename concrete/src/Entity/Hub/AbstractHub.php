<?php

namespace Concrete\Core\Entity\Hub;

use Concrete\Core\Application\UserInterface\Hub\HubInterface;
use Concrete\Core\Entity\PackageTrait;
use Concrete\Core\Package\PackageService;
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
    use PackageTrait;

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
     * @param string|\SimpleXMLElement $pkgHandle
     *
     * @return \Concrete\Core\Entity\Package|null
     */
    protected static function getPackageObject($pkgHandle)
    {
        $pkg = null;
        if ($pkgHandle) {
            $pkgHandle = (string) $pkgHandle;
            if ($pkgHandle !== '') {
                $pkg = app(PackageService::class)->getByHandle($pkgHandle);
            }
        }

        return $pkg;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'identifier' => $this->getIdentifier(),
            'sortOrder' => $this->getSortOrder(),
            'menu' => [
                'title' => $this->getController()->getMenuTitle(),
                'url' => (string) $this->getController()->getHomePageUrl(),
            ],
        ];
    }

    /**
     * @return string
     */
    abstract public function getIdentifier();
}
