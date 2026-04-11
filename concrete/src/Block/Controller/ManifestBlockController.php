<?php
namespace Concrete\Core\Block\Controller;
use Concrete\Core\Block\Manifest\BlockManifest;
use Concrete\Core\Block\Manifest\Serializer\Serializer;
use Concrete\Core\Error\ErrorList\ErrorList;

class ManifestBlockController implements ControllerInterface
{
    public function __construct(
        public BlockManifest $manifest,
        public Serializer $serializer,
    ) {}

    public function validate(array $requestArgs): ErrorList
    {
        return new ErrorList();
    }

    public function save(array $requestArgs): void
    {
        $json = $this->serializer->serializeFromRequest($requestArgs);
        dd($json);
    }

    public function getBlockTypeDefaultSet(): ?string
    {
        return $this->manifest->getSet();
    }

}
