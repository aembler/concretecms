<?php

namespace Concrete\Core\Application\UserInterface\Icon;

use HtmlObject\Element;
use HtmlObject\Traits\Tag;

class FontAwesomeIcon extends AbstractIcon
{
    /** @var string */
    protected $className;

    public function __construct(string $className)
    {
        parent::__construct('font-awesome');
        $this->className = $className;
    }

    public function toHtmlObject(): Tag
    {
        $element = new Element('i');
        $element->addClass($this->className);

        return $element;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'className' => $this->className,
        ]);
    }
}
