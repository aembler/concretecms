<?php

namespace Concrete\Core\Application\UserInterface\Icon;

abstract class AbstractIcon implements IconInterface
{
    /** @var string */
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
        ];
    }
}
