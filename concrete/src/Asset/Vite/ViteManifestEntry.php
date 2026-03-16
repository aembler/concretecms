<?php

namespace Concrete\Core\Asset\Vite;

final class ViteManifestEntry
{
    /**
     * @param string[] $css
     * @param string[] $imports
     */
    public function __construct(
        public readonly string $file,
        public readonly ?string $src = null,
        public readonly array $css = [],
        public readonly array $imports = [],
        public readonly bool $isEntry = false,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['file']) && is_string($data['file']) ? $data['file'] : '',
            isset($data['src']) && is_string($data['src']) ? $data['src'] : null,
            self::filterStrings($data['css'] ?? []),
            self::filterStrings($data['imports'] ?? []),
            isset($data['isEntry']) && is_bool($data['isEntry']) ? $data['isEntry'] : false,
        );
    }

    /**
     * @param mixed $value
     *
     * @return string[]
     */
    protected static function filterStrings(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, static fn ($item): bool => is_string($item)));
    }
}
