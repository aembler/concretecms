<?php

namespace Concrete\Core\Install\StartingPoint;

class StandardPreset implements PresetInterface
{

    public function __construct(
        public readonly string $handle,
        public readonly string $name,
        public readonly string $description,
    ) {}

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function jsonSerialize(): array
    {
        return [
            'handle' => $this->handle,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
        ];
    }


}
