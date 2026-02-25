<?php

namespace Concrete\Core\Application\UserInterface\Icon;

use HtmlObject\Image;
use HtmlObject\Traits\Tag;

class ImageFileIcon extends AbstractIcon
{
    /** @var string */
    protected $src;

    /** @var string */
    protected $alt;

    public function __construct(string $src, string $alt = '')
    {
        parent::__construct('image-file');
        $this->src = $src;
        $this->alt = $alt;
    }

    public function toHtmlObject(): Tag
    {
        $image = Image::create($this->src);
        if ($this->alt !== '') {
            $image->alt($this->alt);
        }

        return $image;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'src' => $this->src,
            'alt' => $this->alt,
        ]);
    }
}
