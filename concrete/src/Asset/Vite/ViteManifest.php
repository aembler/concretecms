<?php

namespace Concrete\Core\Asset\Vite;

final class ViteManifest
{
    /**
     * @param array<string, ViteManifestEntry> $entries
     */
    public function __construct(
        protected array $entries
    ) {
    }

    public function get(string $key): ?ViteManifestEntry
    {
        return $this->entries[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->entries);
    }

    public static function fromJson(string $json): ?self
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        $entries = [];
        foreach ($data as $key => $entry) {
            if (!is_string($key) || !is_array($entry)) {
                continue;
            }

            $entries[$key] = ViteManifestEntry::fromArray($entry);
        }

        return new self($entries);
    }
}
