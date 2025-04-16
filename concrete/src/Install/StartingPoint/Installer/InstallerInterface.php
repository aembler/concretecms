<?php
namespace Concrete\Core\Install\StartingPoint\Installer;

use Concrete\Core\Install\InstallerOptions;
use Concrete\Core\Install\StartingPoint\Controller\ControllerInterface;

interface InstallerInterface
{

    /**
     * @return
     */
    public function getInstallCommands(ControllerInterface $controller, InstallerOptions $options): array;

}
