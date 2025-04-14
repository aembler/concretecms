<?php
namespace Concrete\Core\Install\StartingPoint\Installer\Routine;

use Concrete\Core\Foundation\Command\Traits\HandlerAwareCommandTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractRoutine implements RoutineInterface
{

    use HandlerAwareCommandTrait;

    public function getClass(): string
    {
        return get_class($this);
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, ?string $format = null, array $context = [])
    {
        // Simple classes don't require any options here.
    }

    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = [])
    {
        return [
            'class' => get_class($this),
            'text' => $this->getText(),
        ];
    }


}
