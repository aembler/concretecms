<?php

namespace Concrete\Core\Install\StartingPoint\Installer\Routine;

use Concrete\Core\Foundation\Command\HandlerAwareCommandInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

interface RoutineInterface extends HandlerAwareCommandInterface, NormalizableInterface, DenormalizableInterface
{

    public function getClass(): string;

    public function getText(): ?string;

}
