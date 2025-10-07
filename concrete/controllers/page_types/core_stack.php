<?php
namespace Concrete\Controller\PageType;

use Concrete\Core\Area\Area;
use Concrete\Core\Feature\UsesFeatureInterface;
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

    public function view_contents()
    {
        $this->disableEditing();
    }

    public function view()
    {
        $stack = Stack::getByID($this->c->getCollectionID());
        $this->requireAsset('feature/stacks/backend');
        $this->set('stack', $stack);

        $factory =$this->app->make(DashboardStacksBreadcrumbFactory::class);
        if ($stack->getStackType() === Stack::ST_TYPE_GLOBAL_AREA) {
            $factory->setDisplayGlobalAreasLandingPage(true);
        }
        $breadcrumb = $factory->getBreadcrumb($this->c);

        $this->set('breadcrumb', $this->app->make(ElementManager::class)
            ->get('dashboard/navigation/breadcrumb', [
                'breadcrumb' => $breadcrumb
            ])
        );

    }

}
