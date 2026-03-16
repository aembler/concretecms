<?php

namespace Concrete\Core\Asset\Vite;

use Concrete\Core\Config\Repository\Repository;

class ViteConfig
{
    public function __construct(
        protected Repository $config
    ) {
    }

    public function getBuildDirectory(): string
    {
        return $this->normalizePath((string) $this->config->get('app.vite.build_directory', DIR_BASE . '/build'));
    }

    public function getManifestPath(): string
    {
        return $this->normalizePath((string) $this->config->get('app.vite.manifest', $this->getBuildDirectory() . '/dist/.vite/manifest.json'));
    }

    public function getHotFilePath(): string
    {
        return $this->normalizePath((string) $this->config->get('app.vite.hot_file', $this->getBuildDirectory() . '/hot'));
    }

    public function getDevServerUrl(): ?string
    {
        $url = $this->config->get('app.vite.dev_server_url');
        if (!is_string($url) || $url === '') {
            return null;
        }

        return rtrim($url, '/');
    }

    /**
     * @return array<string, string>
     */
    public function getAliases(): array
    {
        $aliases = $this->config->get('app.vite.aliases', []);
        if (!is_array($aliases)) {
            return [];
        }

        $normalized = [];
        foreach ($aliases as $alias => $path) {
            if (!is_string($alias) || !is_string($path) || $alias === '' || $path === '') {
                continue;
            }

            $normalized[rtrim($alias, '/')] = trim($path, '/');
        }

        return $normalized;
    }

    public function getPublicPath(): string
    {
        $path = $this->config->get('app.vite.public_path', '/build/dist/');
        if (!is_string($path) || $path === '') {
            return '/build/dist/';
        }

        return '/' . trim($path, '/') . '/';
    }

    protected function normalizePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return DIR_BASE . '/' . ltrim($path, '/');
    }
}
