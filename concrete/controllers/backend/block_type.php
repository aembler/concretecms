<?php
namespace Concrete\Controller\Backend;

use Concrete\Controller\Backend\UserInterface as BackendUserInterface;
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
            return new JsonResponse([]);
        } else {
            throw new \UserMessageException(t('Block type does not exist.'));
        }
    }
}
