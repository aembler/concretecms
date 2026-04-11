<?php
namespace Concrete\Core\Block\Controller;

use Concrete\Core\Error\ErrorList\ErrorList;

interface ControllerInterface
{

    public function validate(array $requestArgs): ErrorList;

    public function getBlockTypeDefaultSet(): ?string;

    public function save(array $requestArgs): void;

}
