<?php
namespace Concrete\Core\Install\StartingPoint\Controller;

use Concrete\Core\Install\StartingPoint\PresetInterface;

interface ControllerInterface
{

    public function getHandle(): string;

    public function getName(): string;

    public function getThumbnail(): ?string;

    public function providesThumbnails(): bool;

    /**
     * @return string[]|string
     */
    public function getDescription();

    /**
     * @return PresetInterface[]
     */
    public function getPresets(): array;


}
