<?php

namespace Concrete\Core\Asset\Vite;

class ViteTagRenderer
{
    public function __construct(
        protected ViteResolver $resolver
    ) {
    }

    /**
     * @param string|string[] $entries
     */
    public function render(string|array $entries): string
    {
        $resolved = $this->resolver->resolve($entries);
        $output = [];

        foreach ($resolved->stylesheets as $stylesheet) {
            $output[] = sprintf(
                '<link rel="stylesheet" href="%s" />',
                h($stylesheet)
            );
        }

        foreach ($resolved->scripts as $script) {
            $output[] = sprintf(
                '<script type="module" src="%s"></script>',
                h($script)
            );
        }

        return implode("\n", $output);
    }
}
