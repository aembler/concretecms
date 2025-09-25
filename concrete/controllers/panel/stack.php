<?php
namespace Concrete\Controller\Panel;

use Concrete\Core\Page\Stack\Stack as StackPage;

class Stack extends Page
{
    protected $viewPath = '/panels/stack';

    public function view()
    {
        parent::view();
        $this->set('canEditPagePermissions',
            $this->app->make('config')->get('concrete.permissions.model') != 'simple' &&
            $this->permissions->canEditPagePermissions()
        );
        $stack = StackPage::getByID($this->page->getCollectionID());
        $this->set('isGlobalArea', $stack->getStackType() === StackPage::ST_TYPE_GLOBAL_AREA);
    }
}
