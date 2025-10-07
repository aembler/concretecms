<?php
namespace Concrete\Controller\Dialog\Stack;

use Concrete\Controller\Backend\UserInterface\Page as BackendInterfacePageController;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Command\DeletePageCommand;
use Concrete\Core\Page\EditResponse as PageEditResponse;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\User\User;
use Concrete\Core\Workflow\Progress\Response as WorkflowProgressResponse;
use Concrete\Core\Page\Page;
use Symfony\Component\HttpFoundation\JsonResponse;

class Duplicate extends BackendInterfacePageController
{
    protected $viewPath = '/dialogs/stack/duplicate';

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
        return $this->stack instanceof Stack
            && $this->stack->getStackType() !== Stack::ST_TYPE_GLOBAL_AREA
            && $this->permissions->canMoveOrCopyPage();
    }

    public function view()
    {
        $this->set('stack', $this->stack);
    }

    public function submit(): JsonResponse
    {
        if ($this->validateAction()) {
            $stackName = $this->request->request->get('stackName');
            if (!$this->app->make('helper/validation/strings')->notempty($stackName)) {
                throw new UserMessageException(t('You must give your stack a name.'));
            } else {
                $ns = $this->stack->duplicate();
                $ns->update([
                    'stackName' => $stackName,
                ]);

                $ns->copyLocalizedStacksFrom($this->stack);

                $this->flash('success', t('Stack duplicated successfully.'));
                $response = new PageEditResponse();
                $response->setRedirectURL((string) $ns->getCollectionLink());
                return new JsonResponse($response);
            }
        }
    }
}
