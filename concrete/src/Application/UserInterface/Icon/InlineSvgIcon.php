<?php

namespace Concrete\Core\Application\UserInterface\Icon;

use HtmlObject\Element;
use HtmlObject\Traits\Tag;

class InlineSvgIcon extends AbstractIcon
{
    /** @var string */
    protected $svgMarkup;

    public function __construct(string $svgMarkup)
    {
        parent::__construct('inline-svg');
        $this->svgMarkup = $svgMarkup;
    }

    public function toHtmlObject(): Tag
    {
        $element = new Element('span', $this->svgMarkup);
        $element->addClass('ccm-ui-inline-svg-icon');

        return $element;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'svg' => $this->svgMarkup,
        ]);
    }
}
