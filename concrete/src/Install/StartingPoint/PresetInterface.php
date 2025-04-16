<?php

namespace Concrete\Core\Install\StartingPoint;

interface PresetInterface extends \JsonSerializable
{

    public function getHandle(): string;
    public function getName(): string;

    public function getDescription(): string;

}
