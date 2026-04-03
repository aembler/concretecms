<?php

namespace Concrete\Controller\Element\Dashboard\Navigation;

use Concrete\Core\Controller\ElementController;
use Concrete\Core\Navigation\Subnav\Dashboard\DashboardSubnav;

class Subnav extends ElementController
{
    /**
     * @var \Concrete\Core\Navigation\Subnav\Dashboard\DashboardSubnav
     */
    protected $subnav;

    public function __construct(DashboardSubnav $subnav)
    {
        $this->subnav = $subnav;
        parent::__construct();
    }

    public function getElement()
    {
        return 'dashboard/navigation/subnav';
    }

    public function view()
    {
        $this->set('subnav', $this->subnav);
    }
}
