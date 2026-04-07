<?php

namespace Concrete\Core\Block\BlockType;

use Concrete\Core\Legacy\DatabaseItemList;
use Concrete\Core\Support\Facade\Application;

class BlockTypeList extends DatabaseItemList
{
    protected $autoSortColumns = ['btHandle', 'btID', 'btDisplayOrder'];

    protected $includeInternalBlockTypes = false;

    public function __construct()
    {
        $this->setQuery('select btID from BlockTypes');
        $this->sortByMultiple('btDisplayOrder asc', 'btName asc', 'btID asc');
    }

    public function includeInternalBlockTypes()
    {
        $this->includeInternalBlockTypes = true;
    }

    /**
     * @param int $itemsToGet
     * @param int $offset
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    public function get($itemsToGet = 0, $offset = 0)
    {
        if (!$this->includeInternalBlockTypes) {
            $this->filter('btIsInternal', false);
        }

        $r = parent::get($itemsToGet, (int) $offset);
        $blocktypes = [];
        foreach ($r as $row) {
            $bt = BlockType::getByID($row['btID']);
            if (is_object($bt)) {
                $blocktypes[] = $bt;
            }
        }

        return $blocktypes;
    }

    public function filterByPackage($pkg)
    {
        $this->filter('pkgID', $pkg->getPackageID());
    }

    /**
     * @todo comment this one
     *
     * @param string $xml
     */
    public static function exportList($xml)
    {
        $btl = new static();
        $blocktypes = $btl->get();
        $nxml = $xml->addChild('blocktypes');
        foreach ($blocktypes as $bt) {
            $type = $nxml->addChild('blocktype');
            $type->addAttribute('handle', $bt->getBlockTypeHandle());
            $type->addAttribute('package', $bt->getPackageHandle());
        }
    }

    /**
     * Gets a list of block types that are not installed, used to get blocks that can be installed
     * This function only surveys the web/blocks directory - it's not looking at the package level.
     *
     * @return BlockType[]
     */
    public static function getAvailableList()
    {
        $blocktypes = [];
        $dir = DIR_FILES_BLOCK_TYPES;

        $app = Application::getFacadeApplication();
        $db = $app->make('database/connection');
        $factory = $app->make(BlockTypeEntityFactory::class);

        $btHandles = $db->GetCol('select btHandle from BlockTypes order by btDisplayOrder asc, btName asc, btID asc');

        $aDir = [];
        if (is_dir($dir)) {
            $handle = opendir($dir);
            while (($file = readdir($handle)) !== false) {
                if (strpos($file, '.') === false) {
                    $fdir = $dir . '/' . $file;
                    if (is_dir($fdir) && !in_array($file, $btHandles)) {
                        $bt = BlockType::getByHandle($file);
                        if (!is_object($bt)) {
                            $bt = $factory->createFromDirectory($fdir);
                        }
                        $blocktypes[] = $bt;
                    }
                }
            }
        }

        return $blocktypes;
    }

    /**
     * gets a list of installed BlockTypes.
     *
     * @return BlockType[]
     */
    public static function getInstalledList()
    {
        $btl = new static();

        return $btl->get();
    }

    public static function resetBlockTypeDisplayOrder($column = 'btID')
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database/connection');
        $cache = $app->make('cache');

        $stmt = $db->prepare('UPDATE BlockTypes SET btDisplayOrder = ? WHERE btID = ?');
        $btDisplayOrder = 1;
        $blockTypes = $db->fetchAll("SELECT btID, btHandle, btIsInternal FROM BlockTypes ORDER BY {$column} ASC");
        foreach ($blockTypes as $bt) {
            if ($bt['btIsInternal']) {
                $stmt->execute([0, $bt['btID']]);
            } else {
                $stmt->execute([$btDisplayOrder, $bt['btID']]);
                $btDisplayOrder++;
            }
            $cache->delete('blockTypeByID/' . $bt['btID']);
            $cache->delete('blockTypeByHandle/' . $bt['btHandle']);
        }
        $cache->delete('blockTypeList');
    }
}
