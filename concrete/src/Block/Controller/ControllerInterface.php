<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Error\ErrorList\ErrorList;

interface ControllerInterface
{
    public function ignorePageThemeGridFrameworkContainer(): bool;

    /**
     * @param string $action
     * @param array<mixed> $parameters
     *
     * @return mixed
     */
    public function validate(array $requestArgs): ErrorList;

    public function getBlockTypeDefaultSet(): ?string;

    public function save(array $requestArgs): void;

    public function duplicate(int $newBlockId): void;

}
