<?php

namespace Concrete\Core\Asset\Vite;

final class ViteResolvedAssets
{
    /**
     * @param string[] $stylesheets
     * @param string[] $scripts
     */
    public function __construct(
        public readonly array $stylesheets,
        public readonly array $scripts,
    ) {
    }
}
