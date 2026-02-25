<?php

namespace Concrete\Core\Application\UserInterface\Icon;

use HtmlObject\Traits\Tag;

interface IconInterface extends \JsonSerializable
{
    public function getType(): string;

    public function toHtmlObject(): Tag;
}
