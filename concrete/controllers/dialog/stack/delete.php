<?php
namespace Concrete\Controller\Dialog\Stack;

use Concrete\Controller\Backend\UserInterface\Page as BackendInterfacePageController;
use Concrete\Core\Page\Command\DeletePageCommand;
use Concrete\Core\Page\EditResponse as PageEditResponse;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\User\User;
use Concrete\Core\Workflow\Progress\Response as WorkflowProgressResponse;
use Concrete\Core\Page\Page;
use Symfony\Component\HttpFoundation\JsonResponse;

class Delete extends BackendInterfacePageController
{
    protected $viewPath = '/dialogs/stack/delete';

    /**
     * @var Stack|null
     */
    protected $stack;

    public function on_start()
    {
        parent::on_start();
        $this->stack = Stack::getByID($this->request->get('cID'));
    }

    protected function canAccess()
    {
        return $this->stack instanceof Stack && $this->permissions->canDeletePage();
    }

    public function view()
    {
        $this->set('stack', $this->stack);
    }

    public function submit(): JsonResponse
    {
        if ($this->validateAction()) {
            $c = $this->stack;
            $stackType = $this->stack->getStackType();
            $u = $this->app->make(User::class);
            $command = new DeletePageCommand($c->getCollectionID(), $u->getUserID());
            $response = $this->app->executeCommand($command);
            $pr = new PageEditResponse();
            $pr->setPage($c);
            $parent = Page::getByID($c->getCollectionParentID(), 'ACTIVE');
            if ($response instanceof WorkflowProgressResponse) {
                if ($stackType === Stack::ST_TYPE_GLOBAL_AREA) {
                    $this->flash('success', t('Global area cleared successfully.'));
                } else {
                    $this->flash('success', t('Stack deleted successfully.'));
                }
                $pr->setRedirectURL($parent->getCollectionLink(true));
            } else {
                $pr->setMessage(t('Deletion request saved. This action will have to be approved before the stack is deleted.'));
            }
            return new JsonResponse($pr);
        }
    }
}
