<?php

namespace Concrete\Core\Block\BlockType;

use Concrete\Core\Application\Service\Urls;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;
use Concrete\Core\Application\UserInterface\Icon\ImageFileIcon;
use Concrete\Core\Backup\ContentImporter;
use Concrete\Core\Cache\Level\RequestCache;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Support\Facade\Application;
use Doctrine\ORM\EntityManagerInterface;

class BlockType
{
    public function getBlockTypeIcon(BlockTypeEntity $blockType): IconInterface
    {
        /** @var Urls $urls */
        $urls = app(Urls::class);

        return new ImageFileIcon(
            (string) $urls->getBlockTypeIconURL($blockType),
            (string) $blockType->getBlockTypeName()
        );
    }

    public static function getVersionedBlockTypeDirectory($btHandle, int $version): string
    {
        return DIRNAME_BLOCKS . '/' . (string) $btHandle . '/v' . max(1, $version);
    }

    /**
     * Resolve a block-type relative path, optionally preferring a versioned hierarchy.
     *
     * @param string $btHandle
     * @param string $file Relative file path inside the block directory
     * @param string|false $pkgHandle
     * @param int|null $version
     * @param bool $template
     */
    public static function getBlockTypeRelativePath($btHandle, $file, $pkgHandle = false, ?int $version = null, bool $template = false): string
    {
        $btHandle = trim((string) $btHandle, '/');
        $file = ltrim((string) $file, '/');
        $defaultPath = DIRNAME_BLOCKS . '/' . $btHandle . '/' . $file;
        if ($version === null || $version < 1) {
            return $defaultPath;
        }

        $app = Application::getFacadeApplication();
        $locator = $app->make(FileLocator::class);
        $pkgHandle = (string) $pkgHandle;
        if ($pkgHandle !== '') {
            $locator->addLocation(new FileLocator\PackageLocation($pkgHandle));
        }

        $versionedPath = static::getVersionedBlockTypeDirectory($btHandle, $version) . '/' . $file;
        $record = $locator->getRecord($versionedPath, $template);
        if ($record !== null && $record->exists()) {
            return $versionedPath;
        }

        return $defaultPath;
    }

    /**
     * Get a BlockType given its handle.
     *
     * @param string $btHandle
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType|null
     */
    public static function getByHandle($btHandle)
    {
        $result = null;
        $btHandle = (string) $btHandle;
        if ($btHandle !== '') {
            $app = Application::getFacadeApplication();
            $em = $app->make(EntityManagerInterface::class);
            $repo = $em->getRepository(BlockTypeEntity::class);
            $result = $repo->findOneBy(['btHandle' => $btHandle]);
            if ($result !== null) {
                $result->loadController();
            }
        }

        return $result;
    }

    /**
     * Get a BlockType given its ID.
     *
     * @param int $btID
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType|null
     */
    public static function getByID($btID)
    {
        $app = Application::getFacadeApplication();
        /** @var RequestCache $cache */
        $cache = $app->make('cache/request');
        $key = '/BlockType/' . $btID;
        if ($cache->isEnabled()) {
            $item = $cache->getItem($key);
            if ($item->isHit()) {
                return $item->get();
            }
        }

        $result = null;
        $btID = (int) $btID;
        if ($btID !== 0) {
            $em = $app->make(EntityManagerInterface::class);
            $result = $em->find(BlockTypeEntity::class, $btID);
            if ($result !== null) {
                $result->loadController();
            }
        }

        if (is_object($result) && isset($item) && $item->isMiss()) {
            $item->set($result);
            $cache->save($item);
        }

        return $result;
    }

    /**
     * Install a BlockType that is passed via a btHandle string. The core or override directories are parsed.
     *
     * @param string $btHandle The handle of the block type
     * @param \Concrete\Core\Entity\Package|\Concrete\Core\Package\Package|string|false $pkg The package owning the block type (or its handle)
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType
     */
    public static function installBlockType($btHandle, $pkg = false, string $importMode = ContentImporter::IMPORT_MODE_UPGRADE)
    {
        $app = Application::getFacadeApplication();
        $em = $app->make(EntityManagerInterface::class);
        $pkgHandle = (string) (is_object($pkg) ? $pkg->getPackageHandle() : $pkg);
        $class = static::getBlockTypeMappedClass($btHandle, $pkgHandle, 1);
        $bta = $app->build($class);

        $locator = $app->make(FileLocator::class);
        if ($pkgHandle !== '') {
            $locator->addLocation(new FileLocator\PackageLocation($pkgHandle));
        }
        $dbPath = static::getBlockTypeRelativePath($btHandle, FILENAME_BLOCK_DB, $pkgHandle, 1);
        $path = dirname($locator->getRecord($dbPath)->getFile());

        //Attempt to run the subclass methods (install schema from db.xml, etc.)
        $bta->install($path, $importMode);

        // Prevent the database records being stored in wrong language
        $loc = $app->make(Localization::class);
        $loc->pushActiveContext(Localization::CONTEXT_SYSTEM);
        try {
            //Install the block
            $bt = new BlockTypeEntity();
            $bt->loadFromController($bta);
            if (is_object($pkg)) {
                $bt->setPackageID($pkg->getPackageID());
            }
            $bt->setBlockTypeHandle($btHandle);
            $bt->setBlockTypeActiveVersion(1);
        } finally {
            $loc->popActiveContext();
        }

        $em->persist($bt);
        $em->flush();

        if ($bta->getBlockTypeDefaultSet()) {
            $set = Set::getByHandle($bta->getBlockTypeDefaultSet());
            if ($set !== null) {
                $set->addBlockType($bt);
            }
        }

        return $bt;
    }

    /**
     * Return the class file that this BlockType uses.
     *
     * @param string $btHandle The handle of the block type
     * @param string|false $pkgHandle The handle of the package owning the block type
     *
     * @return string|null
     */
    public static function getBlockTypeMappedClass($btHandle, $pkgHandle = false, ?int $version = null)
    {
        $app = Application::getFacadeApplication();
        $txt = $app->make('helper/text');

        $pkgHandle = (string) $pkgHandle;
        $locator = $app->make(FileLocator::class);
        if ($pkgHandle !== '') {
            $locator->addLocation(new FileLocator\PackageLocation($pkgHandle));
        }

        if ($version !== null && $version > 0) {
            $versionedControllerPath = static::getVersionedBlockTypeDirectory($btHandle, $version) . '/' . FILENAME_CONTROLLER;
            $versionedRecord = $locator->getRecord($versionedControllerPath);
            if ($versionedRecord !== null && $versionedRecord->exists()) {
                $versionedPackageHandle = (string) $versionedRecord->getPackageHandle();
                $versionedPrefix = $versionedRecord->isOverride() ? true : ($versionedPackageHandle !== '' ? $versionedPackageHandle : $pkgHandle);
                $versionedClass = core_class('Block\\' . $txt->camelcase($btHandle) . '\\V' . $version . '\\Controller', $versionedPrefix);
                if (class_exists($versionedClass)) {
                    return $versionedClass;
                }
            }
        }

        $r = $locator->getRecord(DIRNAME_BLOCKS . '/' . $btHandle . '/' . FILENAME_CONTROLLER);
        $overriddenPackageHandle = (string) $r->getPackageHandle();
        if ($overriddenPackageHandle !== '') {
            $pkgHandle = $overriddenPackageHandle;
        }

        $prefix = $r->isOverride() ? true : $pkgHandle;
        $class = core_class('Block\\' . $txt->camelcase($btHandle) . '\\Controller', $prefix);

        return class_exists($class) ? $class : null;
    }

    /**
     * Clears output and record caches.
     */
    public static function clearCache()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make(Connection::class);
        $sm = $db->getSchemaManager();
        $tableNames = array_map('strtolower', $sm->listTableNames());
        if (in_array('config', $tableNames, true)) {
            $platform = $db->getDatabasePlatform();
            foreach ($sm->listTableColumns('Blocks') as $tableColumn) {
                if (strcasecmp($tableColumn->getName(), 'btCachedBlockRecord') === 0) {
                    $db->query('update Blocks set btCachedBlockRecord = null');
                    break;
                }
            }
            if (in_array('collectionversionblocksoutputcache', $tableNames, true)) {
                $db->exec($platform->getTruncateTableSQL('CollectionVersionBlocksOutputCache'));
            }
        }
    }

    /**
     * @deprecated use the installBlockType method
     *
     * @param mixed $btHandle
     * @param mixed $pkg
     *
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType
     */
    public static function installBlockTypeFromPackage($btHandle, $pkg, string $importMode = ContentImporter::IMPORT_MODE_UPGRADE)
    {
        return static::installBlockType($btHandle, $pkg, $importMode);
    }
}
