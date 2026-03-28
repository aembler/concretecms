<?php
namespace Concrete\Core\Application\UserInterface\Hub\Controller;

use Concrete\Core\Application\Application;
use Concrete\Core\Page\Page;
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
        return $this->app->make(PageController::class, [
            'page' => Page::getByPath('/dashboard/pages'),
        ]);
    }

    public function createPageDriver()
    {
        return $this->app->make(PageController::class);
    }

    public function createExpressDriver()
    {
        return $this->app->make(ExpressController::class);
    }

    public function createDashboardWelcomeDriver()
    {
        return $this->app->make(DashboardWelcomeController::class);
    }
}
