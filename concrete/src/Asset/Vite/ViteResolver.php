<?php

namespace Concrete\Core\Asset\Vite;

class ViteResolver
{
    public function __construct(
        protected ViteConfig $config,
        protected VitePathResolver $pathResolver,
        protected ViteManifestLoader $manifestLoader,
    ) {
    }

    /**
     * @param string|string[] $entries
     */
    public function resolve(string|array $entries): ViteResolvedAssets
    {
        $entries = is_array($entries) ? $entries : [$entries];
        $entries = array_values(array_filter($entries, static fn ($entry): bool => is_string($entry) && trim($entry) !== ''));

        if ($entries === []) {
            return new ViteResolvedAssets([], []);
        }

        $devServerUrl = $this->getDevServerUrl();
        if ($devServerUrl !== null) {
            return $this->resolveForDevServer($entries, $devServerUrl);
        }

        return $this->resolveForManifest($entries);
    }

    protected function getDevServerUrl(): ?string
    {
        $configured = $this->config->getDevServerUrl();
        if ($configured !== null) {
            return $configured;
        }

        $hotFile = $this->config->getHotFilePath();
        if (!is_file($hotFile)) {
            return null;
        }

        $contents = @file_get_contents($hotFile);
        if (!is_string($contents)) {
            return null;
        }

        $url = trim($contents);
        if ($url === '') {
            return null;
        }

        return rtrim($url, '/');
    }

    /**
     * @param string[] $entries
     */
    protected function resolveForDevServer(array $entries, string $devServerUrl): ViteResolvedAssets
    {
        $stylesheets = [];
        $publicPath = trim($this->config->getPublicPath(), '/');
        $basePath = $publicPath === '' ? '' : '/' . $publicPath;
        $scripts = [$devServerUrl . $basePath . '/@vite/client'];

        foreach ($entries as $entry) {
            $resolved = $this->pathResolver->resolve($entry);
            $url = $devServerUrl . $basePath . '/' . ltrim($resolved, '/');

            if ($this->isStylesheetPath($resolved)) {
                $stylesheets[$url] = $url;
                continue;
            }

            $scripts[$url] = $url;
        }

        return new ViteResolvedAssets(array_values($stylesheets), array_values($scripts));
    }

    /**
     * @param string[] $entries
     */
    protected function resolveForManifest(array $entries): ViteResolvedAssets
    {
        $manifest = $this->manifestLoader->load();
        if ($manifest === null) {
            return new ViteResolvedAssets([], []);
        }

        $stylesheets = [];
        $scripts = [];
        $publicPath = $this->config->getPublicPath();
        foreach ($entries as $entry) {
            $resolved = $this->pathResolver->resolve($entry);
            $manifestEntry = $manifest->get($resolved);
            if ($manifestEntry === null) {
                continue;
            }

            if ($this->isStylesheetPath($manifestEntry->file)) {
                $stylesheets[$publicPath . ltrim($manifestEntry->file, '/')] = $publicPath . ltrim($manifestEntry->file, '/');
                continue;
            }

            $scripts[$publicPath . ltrim($manifestEntry->file, '/')] = $publicPath . ltrim($manifestEntry->file, '/');
            foreach ($this->collectCssImports($manifest, $resolved) as $cssFile) {
                $stylesheets[$publicPath . ltrim($cssFile, '/')] = $publicPath . ltrim($cssFile, '/');
            }
        }

        return new ViteResolvedAssets(array_values($stylesheets), array_values($scripts));
    }

    /**
     * @return string[]
     */
    protected function collectCssImports(ViteManifest $manifest, string $entry, array &$visited = []): array
    {
        if (isset($visited[$entry])) {
            return [];
        }
        $visited[$entry] = true;

        $manifestEntry = $manifest->get($entry);
        if ($manifestEntry === null) {
            return [];
        }

        $css = [];
        foreach ($manifestEntry->imports as $import) {
            foreach ($this->collectCssImports($manifest, $import, $visited) as $cssFile) {
                $css[$cssFile] = $cssFile;
            }
        }

        foreach ($manifestEntry->css as $cssFile) {
            $css[$cssFile] = $cssFile;
        }

        return array_values($css);
    }

    protected function isStylesheetPath(string $path): bool
    {
        return (bool) preg_match('/\.(css|scss|sass|less|styl|stylus|pcss|postcss)$/i', $path);
    }
}
