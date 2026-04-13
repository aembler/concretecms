<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Filesystem\FileLocator;

final class Locator
{
    public function __construct(private FileLocator $fileLocator) {}

    public function getRecord(BlockType|Block $object): FileLocator\Record
    {
        // Manifest block type support
        if ($object->getPackageHandle() !== '') {
            $this->fileLocator->addLocation(new FileLocator\PackageLocation($object->getPackageHandle()));
        }
        $record = $this->fileLocator->getRecord(
            DIRNAME_BLOCKS .
            DIRECTORY_SEPARATOR .
            $object->getBlockTypeHandle() .
            DIRECTORY_SEPARATOR .
            FILENAME_BLOCK_MANIFEST
        );
        return $record;
    }

}
