<?php

namespace Concrete\Core\Asset\Vite;

class ViteManifestLoader
{
    /**
     * @var array<string, ViteManifest|null>
     */
    protected static array $cache = [];

    public function __construct(
        protected ViteConfig $config
    ) {
    }

    public function load(): ?ViteManifest
    {
        $path = $this->config->getManifestPath();
        $cacheKey = realpath($path) ?: $path;
        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }

        if (!is_file($path)) {
            return self::$cache[$cacheKey] = null;
        }

        $contents = @file_get_contents($path);
        if (!is_string($contents)) {
            return self::$cache[$cacheKey] = null;
        }

        return self::$cache[$cacheKey] = ViteManifest::fromJson($contents);
    }
}
