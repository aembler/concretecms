<?php
namespace Concrete\Controller\Panel;

use Concrete\Core\Attribute\Set as AttributeSet;
use Concrete\Core\Page\Page as ConcretePage;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Permission\Checker as Permissions;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\Support\Facade\Config;
use Symfony\Component\HttpFoundation\JsonResponse;

class Page
{
    public function view(): JsonResponse
    {
        $page = $this->resolvePage();
        if ($page === null || $page->isError()) {
            return new JsonResponse([
                'error' => t('Unable to find the specified page.'),
            ], 404);
        }

        $checker = new Permissions($page);
        $permissionKey = PermissionKey::getByHandle('edit_page_properties');
        $permissionKey->setPermissionObject($page);
        $assignment = $permissionKey->getMyAssignment();
        $seoSet = AttributeSet::getByHandle('seo');
        $pageType = PageType::getByID($page->getPageTypeID());

        $allowedAttributeKeys = [];
        if (is_object($assignment)) {
            $allowedAttributeKeys = $assignment->getAttributesAllowedArray();
        }

        return new JsonResponse([
            'pageId' => (int) $page->getCollectionID(),
            'permissions' => [
                'composer' => is_object($pageType) && $checker->canEditPageContents(),
                'design' => $checker->canEditPageTheme() || $checker->canEditPageTemplate(),
                'seo' => $checker->canEditPageProperties() && is_object($seoSet),
                'location' => is_object($assignment) && $assignment->allowEditPaths(),
                'attributes' => $checker->canEditPageProperties() && count($allowedAttributeKeys) > 0,
                'caching' => $checker->canEditPageSpeedSettings(),
                'permissions' => $checker->canEditPagePermissions(),
                'versions' => $checker->canViewPageVersions(),
                'mobilePreview' => $checker->canViewPageVersions(),
                'viewAsUser' => $checker->canPreviewPageAsUser() && Config::get('concrete.permissions.model') === 'advanced',
                'delete' => $checker->canDeletePage(),
            ],
        ]);
    }

    private function resolvePage(): ?ConcretePage
    {
        $request = \Request::getInstance();
        $cID = $request->query->get('cID');
        if (!$cID) {
            $cID = $request->request->get('cID');
        }
        if ($cID) {
            return ConcretePage::getByID((int) $cID);
        }

        $currentPage = $request->getCurrentPage();
        if ($currentPage instanceof ConcretePage) {
            return $currentPage;
        }

        return null;
    }
}
