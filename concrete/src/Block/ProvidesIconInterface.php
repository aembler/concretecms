<?php
namespace Concrete\Core\Block;

use Concrete\Core\Block\BlockType\Editor\EditorFactory;
use Concrete\Core\Block\BlockType\Editor\EditorInterface;
use Concrete\Core\Application\UserInterface\Icon\IconInterface;
interface ProvidesIconInterface
{

    public function getIcon(): IconInterface;

}
