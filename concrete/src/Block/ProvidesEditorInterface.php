<?php
namespace Concrete\Core\Block;

use Concrete\Core\Block\BlockType\Editor\EditorFactory;
use Concrete\Core\Block\BlockType\Editor\EditorInterface;

interface ProvidesEditorInterface
{

    /**
     * @param string $mode EditorFactory::MODE_ADD|EditorFactory::MODE_EDIT
     * @return mixed
     */
    public function getEditor(string $mode): ?EditorInterface;
}
