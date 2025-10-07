<?php
namespace Concrete\Controller\Panel\Detail\Stack;

use Concrete\Controller\Backend\UserInterface\Page as BackendInterfacePageController;
use Concrete\Core\Entity\Statistics\UsageTracker\StackUsageRecord;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Doctrine\ORM\EntityManagerInterface;

class Usage extends BackendInterfacePageController
{
    protected $viewPath = '/panels/details/stack/usage';

    public function canAccess()
    {
        return $this->permissions->canViewPageInSitemap();
    }

    /**
     * Generator for transforming a list of StackUsageRecords into Collection objects
     * This method can be used to do some interesting things with the list two sine it is ordered
     * by Collection ID /and/ Collection Version ID.
     *
     * @param StackUsageRecord[] $records
     *
     * @return \Generator
     */
    protected function getUsageGenerator(array $records)
    {
        $last_collection = null;

        foreach ($records as $record) {
            if ($last_collection && $last_collection->getCollectionID() == $record->getCollectionId()) {
                // This is the same collection as the last collection, lets use it again.
                $collection = $last_collection;
            } else {
                /** @var \Concrete\Core\Page\Collection\Collection $collection */
                $last_collection = $collection = Page::getByID($record->getCollectionId());
            }

            // Load in the version object
            $collection->loadVersionObject($record->getCollectionVersionId());

            // Yield the collection object
            yield $collection;
        }
    }

    public function view()
    {
        $stack = Stack::getByID($this->page->getCollectionID());
        if ($stack instanceof Stack) {
            $entityManager = $this->app->make(EntityManagerInterface::class);
            $repository = $entityManager->getRepository(StackUsageRecord::class);

            $records = $repository->findBy([
                'stack_id' => $stack->getCollectionID(),
            ]);

            $this->set('records', $this->getUsageGenerator($records));
        } else {
            throw new \Exception(t('Invalid stack object.'));
        }
    }

}
