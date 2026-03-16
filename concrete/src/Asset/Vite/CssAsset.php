<?php

namespace Concrete\Core\Asset\Vite;

use Concrete\Core\Asset\CssAsset as BaseCssAsset;
use Concrete\Core\Html\Object\HeadLink;

class CssAsset extends BaseCssAsset
{
    use ResolvesAssetsTrait;

    public function getAssetURL()
    {
        return $this->getResolvedStylesheets()[0] ?? '';
    }

    public function __toString()
    {
        $stylesheets = $this->getResolvedStylesheets();
        if ($stylesheets === []) {
            return '';
        }

        $output = [];
        foreach ($stylesheets as $stylesheet) {
            $output[] = (string) new HeadLink($stylesheet, 'stylesheet', 'text/css', $this->getAssetMedia());
        }

        return implode("\n", $output);
    }
}
