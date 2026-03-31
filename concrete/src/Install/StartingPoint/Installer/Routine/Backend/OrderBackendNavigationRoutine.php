<?php
namespace Concrete\Core\Install\StartingPoint\Installer\Routine\Backend;

use Concrete\Core\Install\StartingPoint\Installer\Routine\AbstractRoutine;

class OrderBackendNavigationRoutine extends AbstractRoutine
{

    public function getText(): string
    {
        return t('Ordering Backend Navigation');
    }


}
