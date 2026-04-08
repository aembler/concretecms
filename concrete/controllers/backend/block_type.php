<?php
namespace Concrete\Controller\Backend;

use Concrete\Controller\Backend\UserInterface as BackendUserInterface;
use Concrete\Core\Block\BlockType\BlockTypeEntityFactory;
use Concrete\Core\Block\Manifest\BlockManifestParser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlockType
{
    /**
     * Renders the block view, normally called via an ajax request
     * path: /ccm/system/block/render
     * @return void
     */
    public function getManifest($blockTypeId): JsonResponse
    {
        $blockType = \Concrete\Core\Block\BlockType\BlockType::getByID($blockTypeId);
        if ($blockType) {
            $directory = app(BlockTypeEntityFactory::class)->getDirectoryByHandle(
                (string) $blockType->getBlockTypeHandle(),
                (string) $blockType->getPackageHandle()
            );
            $manifest = app(BlockManifestParser::class)->parseFile($directory . '/' . FILENAME_BLOCK_MANIFEST);

            return new JsonResponse($manifest);
        } else {
            throw new \UserMessageException(t('Block type does not exist.'));
        }
    }
}
