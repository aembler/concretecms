<?php
namespace Concrete\Controller\Backend;

use Concrete\Controller\Backend\UserInterface as BackendUserInterface;
use Concrete\Core\Block\BlockType\BlockTypeEntityFactory;
use Concrete\Core\Block\Controller\ManifestBlockController;
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
            $controller = $blockType->getController();
            if (!($controller instanceof ManifestBlockController)) {
                throw new \UserMessageException(t('This block type does not use a manifest.'));
            }
            return new JsonResponse($controller->manifest);
        } else {
            throw new \UserMessageException(t('Block type does not exist.'));
        }
    }
}
