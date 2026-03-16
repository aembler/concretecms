<?php

namespace Concrete\Core\Asset\Vite;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViteTwigExtension extends AbstractExtension
{
    public function __construct(
        protected ViteTagRenderer $renderer
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string|string[] $entries
     */
    public function render(string|array $entries): string
    {
        return $this->renderer->render($entries);
    }
}
