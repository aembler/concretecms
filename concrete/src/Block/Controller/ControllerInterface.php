<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Error\ErrorList\ErrorList;

interface ControllerInterface
{

    public function on_start(): void;

    public function on_before_render(): void;

    public function getSets(): array;

    public function ignorePageThemeGridFrameworkContainer(): bool;

    public function runAction($method, $parameters): void;

    public function validate(array $requestArgs): ErrorList;

    public function getBlockTypeDefaultSet(): ?string;

    public function save(array $requestArgs): void;

}
