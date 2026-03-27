<?php
namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\Application;
use Concrete\Core\Support\Manager as CoreManager;

defined('C5_EXECUTE') or die("Access Denied.");

class Manager extends CoreManager
{
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function createPagesDriver()
    {
        return $this->app->make(PageController::class);
    }



}
