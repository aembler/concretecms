<?php

namespace Concrete\Core\Asset\Vite;

use Concrete\Core\Asset\JavascriptAsset as BaseJavascriptAsset;
use HtmlObject\Element;

class JavascriptAsset extends BaseJavascriptAsset
{
    use ResolvesAssetsTrait;

    public function getAssetURL()
    {
        return $this->getResolvedScripts()[0] ?? '';
    }

    public function __toString()
    {
        $scripts = $this->getResolvedScripts();
        if ($scripts === []) {
            return '';
        }

        $output = [];
        foreach ($scripts as $script) {
            $element = new Element('script');
            $element->setAttribute('type', 'module');
            $element->setAttribute('src', $script);
            $output[] = (string) $element;
        }

        return implode("\n", $output);
    }
}
