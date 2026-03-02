<?php

namespace Concrete\Core\Block\BlockType\Editor;

interface EditorInterface extends \JsonSerializable
{

    public function getComponentKey(): string;

    public function getComponentProps(): array;

}
