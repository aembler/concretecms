<?php

namespace Concrete\Core\Block\BlockType;

use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Support\Facade\Application;
use RuntimeException;
use SimpleXMLElement;

class BlockTypeEntityFactory
{
    public function getDirectoryByHandle(string $handle, string $pkgHandle = ''): string
    {
        $app = Application::getFacadeApplication();
        $locator = $app->make(FileLocator::class);
        if ($pkgHandle !== '') {
            $locator->addLocation(new FileLocator\PackageLocation($pkgHandle));
        }

        foreach ([FILENAME_BLOCK_MANIFEST, FILENAME_BLOCK_CONTROLLER] as $filename) {
            $record = $locator->getRecord(DIRNAME_BLOCKS . '/' . $handle . '/' . $filename);
            if ($record !== null && $record->exists()) {
                return dirname($record->getFile());
            }
        }

        throw new RuntimeException(sprintf('Unable to locate a block type directory for handle "%s".', $handle));
    }

    public function directoryHasController(string $directory): bool
    {
        return is_file($directory . '/' . FILENAME_BLOCK_CONTROLLER);
    }

    public function createFromDirectory(string $directory): BlockTypeEntity
    {
        if (!is_dir($directory)) {
            throw new RuntimeException(sprintf('The block type directory "%s" does not exist.', $directory));
        }

        $handle = basename($directory);
        $manifestFile = $directory . '/' . FILENAME_BLOCK_MANIFEST;
        $controllerFile = $directory . '/' . FILENAME_BLOCK_CONTROLLER;

        if (is_file($manifestFile)) {
            return $this->createFromManifestFile($handle, $manifestFile);
        }

        if (is_file($controllerFile)) {
            return $this->createFromController($handle);
        }

        throw new RuntimeException(sprintf(
            'The block type directory "%s" must contain either "%s" or "%s".',
            $directory,
            FILENAME_BLOCK_CONTROLLER,
            FILENAME_BLOCK_MANIFEST
        ));
    }

    protected function createFromController(string $handle): BlockTypeEntity
    {
        $bt = new BlockTypeEntity();
        $bt->setBlockTypeHandle($handle);

        $class = $bt->getBlockTypeClass();
        $controller = Application::getFacadeApplication()->build($class);
        $bt->setBlockTypeName($controller->getBlockTypeName());
        $bt->setBlockTypeDescription($controller->getBlockTypeDescription());

        return $bt;
    }

    protected function createFromManifestFile(string $expectedHandle, string $manifestFile): BlockTypeEntity
    {
        $xml = simplexml_load_file($manifestFile);
        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException(sprintf('Unable to parse block manifest "%s".', $manifestFile));
        }

        if (!isset($xml->blocktype)) {
            throw new RuntimeException(sprintf('The block manifest "%s" does not define a <blocktype> element.', $manifestFile));
        }

        $blockType = $xml->blocktype;
        $manifestHandle = trim((string) $blockType['handle']);
        if ($manifestHandle === '') {
            throw new RuntimeException(sprintf('The block manifest "%s" must define a blocktype handle.', $manifestFile));
        }

        if ($manifestHandle !== $expectedHandle) {
            throw new RuntimeException(sprintf(
                'The block manifest "%s" defines handle "%s", which does not match the directory name "%s".',
                $manifestFile,
                $manifestHandle,
                $expectedHandle
            ));
        }

        $bt = new BlockTypeEntity();
        $bt->setBlockTypeHandle($manifestHandle);
        $bt->setBlockTypeName((string) $blockType['name']);
        $bt->setBlockTypeDescription((string) $blockType['description']);

        return $bt;
    }
}
