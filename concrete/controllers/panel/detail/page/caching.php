<?php
namespace Concrete\Controller\Panel\Detail\Page;

use Concrete\Controller\Backend\UserInterface\Page as BackendInterfacePageController;
use Concrete\Core\Cache\Page\PageCacheRecord;
use Concrete\Core\Cache\Page\UnknownPageCacheRecord;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Core;
use Concrete\Core\Support\Facade\Loader;
use PageEditResponse;
use PageCache;
use Symfony\Component\HttpFoundation\JsonResponse;

class Caching extends BackendInterfacePageController
{
    protected $viewPath = '/panels/details/page/caching';

    protected function canAccess()
    {
        return $this->permissions->canEditPageSpeedSettings();
    }

    public function view()
    {
        switch (Config::get('concrete.cache.pages')) {
            case 'blocks':
                $globalSetting = t('cache page if all blocks support it.');
                $enableCache = true;
                break;
            case 'all':
                $globalSetting = t('enable full page cache.');
                $enableCache = true;
                break;
            default:
                $globalSetting = t('disable full page cache.');
                $enableCache = false;
                break;
        }

        switch (Config::get('concrete.cache.full_page_lifetime')) {
            case 'default':
                $globalSettingLifetime = Loader::helper('date')->describeInterval(Config::get('concrete.cache.lifetime'));
                break;
            case 'custom':
                $globalSettingLifetime = Loader::helper('date')->describeInterval(Config::get('concrete.cache.full_page_lifetime_value') * 60);
                break;
            default:
                $globalSettingLifetime = t('Until manually cleared');
                break;
        }

        $cache = PageCache::getLibrary();
        $record = $cache->getRecord($this->page);

        $cacheState = 'not_cached';
        $cacheMessage = t('This page is not currently in the full page cache.');
        $cacheExpiresAt = null;
        $canPurge = false;

        if ($record instanceof PageCacheRecord) {
            $cacheState = 'cached';
            $cacheMessage = t('This page currently exists in the full page cache.');
            $cacheExpiresAt = Core::make('date')->formatDateTime($record->getCacheRecordExpiration());
            $canPurge = true;
        } elseif ($record instanceof UnknownPageCacheRecord) {
            $cacheState = 'unknown';
            $cacheMessage = t('This page may exist in the page cache.');
            $canPurge = true;
        }

        $customLifetimeValue = null;
        if ($this->page->getCollectionFullPageCachingLifetimeCustomValue() > 0 && $this->page->getCollectionFullPageCachingLifetime()) {
            $customLifetimeValue = (int) $this->page->getCollectionFullPageCachingLifetimeCustomValue();
        }

        return new JsonResponse([
            'pageId' => $this->page->getCollectionID(),
            'global' => [
                'cacheEnabled' => $enableCache,
                'mode' => (string) Config::get('concrete.cache.pages'),
                'modeLabel' => $globalSetting,
                'lifetimeMode' => (string) Config::get('concrete.cache.full_page_lifetime'),
                'lifetimeLabel' => $globalSettingLifetime,
            ],
            'form' => [
                'cacheMode' => (string) $this->page->getCollectionFullPageCaching(),
                'lifetimeMode' => (string) $this->page->getCollectionFullPageCachingLifetime(),
                'customLifetimeMinutes' => $customLifetimeValue,
            ],
            'status' => [
                'state' => $cacheState,
                'message' => $cacheMessage,
                'expiresAt' => $cacheExpiresAt,
                'canPurge' => $canPurge,
            ],
            'actions' => [
                'submitUrl' => $this->action('submit'),
                'purgeUrl' => $this->action('purge'),
            ],
        ]);
    }

    public function purge()
    {
        $cache = PageCache::getLibrary();
        $cache->purge($this->page);
        $r = new PageEditResponse();
        $r->setPage($this->page);
        $r->setTitle(t('Page Updated'));
        $r->setMessage(t('This page has been purged from the full page cache.'));
        $r->outputJSON();
    }

    public function submit()
    {
        if ($this->validateAction()) {
            $data = [];
            $data['cCacheFullPageContent'] = $this->request->post('cCacheFullPageContent');
            $data['cCacheFullPageContentLifetimeCustom'] = $this->request->post('cCacheFullPageContentLifetimeCustom');
            $data['cCacheFullPageContentOverrideLifetime'] = $this->request->post('cCacheFullPageContentOverrideLifetime');
            $this->page->update($data);
            $r = new PageEditResponse();
            $r->setPage($this->page);
            $r->setTitle(t('Page Updated'));
            $r->setMessage(t('Full page caching settings saved.'));
            $r->outputJSON();
        }
    }
}
