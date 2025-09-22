<?php
namespace Concrete\Controller\PageType;

use Concrete\Core\Page\Stack\Folder\FolderService;

class CoreStackCategory extends CoreStack
{

    public function view()
    {
        $folder = $this->app->make(FolderService::class)
            ->getByID($this->c->getCollectionID());
        if ($folder) {
            return $this->buildRedirect([
                '/dashboard/blocks/stacks', 'view_details', $folder->getPage()->getCollectionID()
            ]);
        } else {
            return $this->factory->notFound('');
        }
    }

}
