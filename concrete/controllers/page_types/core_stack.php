<?php
namespace Concrete\Controller\PageType;

use Concrete\Core\Filesystem\ElementManager;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Navigation\Breadcrumb\Dashboard\DashboardStacksBreadcrumbFactory;
use Concrete\Core\Page\Controller\PageTypeController;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Permission\Checker as Permissions;

class CoreStack extends PageTypeController
{

    /**
     * @var \Concrete\Core\Http\ResponseFactory
     */
    protected $factory;

    public function __construct(\Concrete\Core\Page\Page $c, ResponseFactory $factory)
    {
        parent::__construct($c);
        $this->factory = $factory;
    }

    public function on_start()
    {
        $stacksPage = Page::getByPath(STACKS_LISTING_PAGE_PATH);
        $stacksPerms = new Permissions($stacksPage);
        if (!$stacksPerms->canViewPage()) {
            return $this->factory->notFound('');
        }
    }

    public function view()
    {
        $this->set('stack', Stack::getByID($this->c->getCollectionID()));
        $breadcrumb =$this->app->make(
            DashboardStacksBreadcrumbFactory::class
        )->getBreadcrumb($this->c);

        $this->set('breadcrumb', $this->app->make(ElementManager::class)
            ->get('dashboard/navigation/breadcrumb', [
                'breadcrumb' => $breadcrumb
            ])
        );

    }

}
