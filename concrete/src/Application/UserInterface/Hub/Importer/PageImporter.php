<?php

namespace Concrete\Core\Application\UserInterface\Hub\Importer;

use Concrete\Core\Entity\Hub\PageHub;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Page;

class PageImporter implements ImporterInterface
{
    public function createFromImport(\SimpleXMLElement $node): \Concrete\Core\Application\UserInterface\Hub\HubInterface
    {
        if (!isset($node->page)) {
            throw new \RuntimeException('Page hubs require a <page> child node.');
        }

        $pageNode = $node->page;
        $page = null;
        $path = trim((string) $pageNode['path']);
        if ($path !== '') {
            $page = Page::getByPath($path, 'ACTIVE');
        }

        if (!$page || $page->isError()) {
            throw new \RuntimeException('Unable to resolve the page referenced by this page hub import node.');
        }

        $label = null;
        if (!empty($pageNode['label'])) {
            $label = (string) $pageNode['label'];
        }
        $hub = new PageHub($page, $label);
        return $hub;
    }
}
