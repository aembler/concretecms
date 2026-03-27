<?php

namespace Concrete\Core\Application\UserInterface\Hub;

use Concrete\Core\Entity\Hub\AbstractHub;
use Concrete\Core\Foundation\Command\Command;

class CreateHubCommand extends Command
{
    /**
     * @var \Concrete\Core\Entity\Hub\AbstractHub
     */
    protected $hub;

    public function __construct(AbstractHub $hub)
    {
        $this->hub = $hub;
    }

    /**
     * @return \Concrete\Core\Entity\Hub\AbstractHub
     */
    public function getHub()
    {
        return $this->hub;
    }
}
