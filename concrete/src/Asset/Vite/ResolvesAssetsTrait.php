<?php

namespace Concrete\Core\Asset\Vite;

trait ResolvesAssetsTrait
{
    protected string $viteEntry = '';

    public function register($filename, $args, $pkg = false)
    {
        $this->viteEntry = (string) $filename;

        parent::register($filename, [
            'local' => false,
            'combine' => false,
            'minify' => false,
            'version' => null,
            'position' => $args['position'] ?? null,
        ], $pkg);
    }

    protected function resolveViteAssets(): ViteResolvedAssets
    {
        return app(ViteResolver::class)->resolve($this->viteEntry);
    }

    /**
     * @return string[]
     */
    protected function getResolvedStylesheets(): array
    {
        return $this->resolveViteAssets()->stylesheets;
    }

    /**
     * @return string[]
     */
    protected function getResolvedScripts(): array
    {
        return $this->resolveViteAssets()->scripts;
    }

    public function getAssetHashKey()
    {
        return 'vite:' . static::class . ':' . $this->viteEntry;
    }

    public function getAssetContents()
    {
        return null;
    }
}
