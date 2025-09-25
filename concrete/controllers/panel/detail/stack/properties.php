<?php
namespace Concrete\Controller\Panel\Detail\Stack;

use Concrete\Controller\Backend\UserInterface\Page as BackendInterfacePageController;
use Concrete\Core\Page\EditResponse;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Permission\Key\Key;
use Concrete\Core\User\User;
use Concrete\Core\Workflow\Request\ApproveStackRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Workflow\Progress\Response as WorkflowProgressResponse;

class Properties extends BackendInterfacePageController
{
    protected $viewPath = '/panels/details/stack/properties';

    protected $assignment;

    protected $canEditName = false;

    public function on_start()
    {
        parent::on_start();
        $stack = Stack::getByID($this->page->getCollectionID());
        $isGlobalArea = $stack->getStackType() === Stack::ST_TYPE_GLOBAL_AREA;

        $pk = Key::getByHandle('edit_page_properties');
        $pk->setPermissionObject($this->page);
        $this->assignment = $pk->getMyAssignment();
        $this->canEditName = $this->assignment->allowEditName() && !$isGlobalArea;
        $this->set('assignment', $this->assignment);
    }

    public function canAccess()
    {
        return $this->permissions->canEditPageProperties();
    }

    public function view()
    {
        $this->set('canEditName', $this->canEditName);
    }

    public function submit(): JsonResponse
    {
        if ($this->validateAction()) {
            if ($this->canEditName) {
                $name = $this->request->request->get('stackName');
                $nvc = $this->page->getVersionToModify();
                $nvc->update(['cName' => $name]);

                $pkr = new ApproveStackRequest();
                $user = $this->app->make(User::class);
                $pkr->setRequestedPage($nvc);
                $pkr->setRequestedVersionID($nvc->getVersionID());
                $pkr->setRequesterUserID($user->getUserID());
                $response = $pkr->trigger();

                $r = new EditResponse();
                $r->setPage($this->page);
                if ($response instanceof WorkflowProgressResponse) {
                    $updatedPage = Page::getByID($nvc->getCollectionID(), 'APPROVED');
                    $r->setRedirectURL($this->app->make('url/resolver/page')->resolve([$updatedPage]));
                    $this->flash('success', t('Stack renamed successfully.'));
                } else {
                    $r->setTitle(t('Stack Update Requested'));
                    $r->setMessage(t('The stack rename request has been submitted to workflow.'));
                }
                return new JsonResponse($r);
            }
        }
    }

}
