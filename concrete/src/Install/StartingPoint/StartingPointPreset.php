<?php

namespace Concrete\Core\Install\StartingPoint;

class StartingPointPreset implements \JsonSerializable
{

    public function __construct(
        public readonly string $contentFile,
        public readonly string $name,
        public readonly string $description,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'contentFile' => $this->contentFile,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

}
