<?php

namespace Concrete\Tests\Navigation;

use Concrete\Core\Application\Service\Dashboard;
use Concrete\Core\Navigation\Subnav\Dashboard\DashboardSubnav;
use Concrete\Core\Navigation\Subnav\Dashboard\DashboardSubnavFactory;
use Concrete\Core\Page\Page;
use Concrete\Tests\TestCase;
use Mockery as M;

class DashboardSubnavFactoryTest extends TestCase
{
    public function testExcludedPagesAreOmittedFromSubnav()
    {
        $currentPage = M::mock(Page::class);
        $currentPage->shouldReceive('getCollectionPath')->andReturn('/dashboard/system/seo/urls');
        $currentPage->shouldReceive('getCollectionParentID')->andReturn(12);
        $currentPage->shouldReceive('getCollectionID')->andReturn(42);

        $visibleChild = M::mock(Page::class);
        $visibleChild->shouldReceive('getAttribute')->with('exclude_nav')->andReturn(false);
        $visibleChild->shouldReceive('getCollectionLink')->andReturn('/dashboard/system/seo');
        $visibleChild->shouldReceive('getCollectionName')->andReturn('SEO');
        $visibleChild->shouldReceive('getCollectionID')->andReturn(41);

        $activeChild = M::mock(Page::class);
        $activeChild->shouldReceive('getAttribute')->with('exclude_nav')->andReturn(false);
        $activeChild->shouldReceive('getCollectionLink')->andReturn('/dashboard/system/seo/urls');
        $activeChild->shouldReceive('getCollectionName')->andReturn('URLs');
        $activeChild->shouldReceive('getCollectionID')->andReturn(42);

        $excludedChild = M::mock(Page::class);
        $excludedChild->shouldReceive('getAttribute')->with('exclude_nav')->andReturn(true);

        $parentPage = M::mock('alias:' . Page::class);
        $parentPage->shouldReceive('getByID')->with(12, 'ACTIVE')->andReturn($parentPage);
        $parentPage->shouldReceive('isError')->andReturn(false);
        $parentPage->shouldReceive('getCollectionChildren')->with('ACTIVE')->andReturn([
            $visibleChild,
            $excludedChild,
            $activeChild,
        ]);

        $dashboard = M::mock(Dashboard::class);
        $dashboard->shouldReceive('inDashboard')->with($parentPage)->andReturn(true);

        $factory = new DashboardSubnavFactory($dashboard);
        $subnav = $factory->getSubnav($currentPage);

        $this->assertInstanceOf(DashboardSubnav::class, $subnav);
        $this->assertCount(2, $subnav->getItems());
        $this->assertSame('SEO', $subnav->getItems()[0]->getName());
        $this->assertSame('URLs', $subnav->getItems()[1]->getName());
        $this->assertTrue($subnav->getItems()[1]->isActive());
    }
}
