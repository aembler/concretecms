<?php

namespace Concrete\Core\Asset\Vite;

class VitePathResolver
{
    public function __construct(
        protected ViteConfig $config
    ) {
    }

    public function resolve(string $input): string
    {
        $input = trim($input);
        if ($input === '') {
            throw new \InvalidArgumentException('Vite entry path cannot be empty.');
        }

        foreach ($this->config->getAliases() as $alias => $target) {
            if ($input === $alias || str_starts_with($input, $alias . '/')) {
                $suffix = ltrim(substr($input, strlen($alias)), '/');
                return trim($target . '/' . $suffix, '/');
            }
        }

        if (str_starts_with($input, '@')) {
            throw new \InvalidArgumentException(sprintf('Unknown Vite path alias "%s".', $input));
        }

        return ltrim($input, '/');
    }
}
