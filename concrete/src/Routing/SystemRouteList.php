<?php

namespace Concrete\Core\Routing;

use Concrete\Core\Http\Middleware\ValidateSimpleCsrfTokenMiddleware;

class SystemRouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $system = $router->buildGroup()->addMiddleware(ValidateSimpleCsrfTokenMiddleware::class);

        $system->buildGroup()->setPrefix('/ccm/system/panels')->setNamespace('Concrete\Controller\Panel')
            ->routes('panels.php')
        ;

        $system->buildGroup()->routes('panels/details.php');

        $system->buildGroup()->setNamespace('Concrete\Controller\Frontend')->setPrefix('/ccm/assets/localization')
            ->routes('assets_localization.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Backend')->setPrefix('/ccm/system/block')
            ->routes('actions/blocks.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Backend')->setPrefix('/ccm/system/page')
            ->routes('actions/pages.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Backend')->setPrefix('/ccm/system/user')
            ->routes('actions/users.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Backend')->setPrefix('/ccm/system/group')
            ->routes('actions/groups.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Backend')->setPrefix('/ccm/system/file')
            ->routes('actions/files.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Backend\Board')->setPrefix('/ccm/system/board')
            ->routes('actions/boards.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Conversation')->setPrefix('/ccm/system/dialogs/conversation')
            ->routes('dialogs/conversations.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Type')
            ->setPrefix('/ccm/system/dialogs/type')
            ->routes('dialogs/page_types.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\User')
            ->setPrefix('/ccm/system/dialogs/user')
            ->routes('dialogs/users.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Groups')
            ->setPrefix('/ccm/system/dialogs/groups')
            ->routes('dialogs/groups.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Page')
            ->setPrefix('/ccm/system/dialogs/page')
            ->routes('dialogs/pages.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Logs')
            ->setPrefix('/ccm/system/dialogs/logs')
            ->routes('dialogs/logs.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Permissions')
            ->setPrefix('/ccm/system/dialogs/permissions')
            ->routes('dialogs/permissions.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\File')
            ->setPrefix('/ccm/system/dialogs/file')
            ->routes('dialogs/files.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Area')
            ->setPrefix('/ccm/system/dialogs/area')
            ->routes('dialogs/areas.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Block')
            ->setPrefix('/ccm/system/dialogs/block')
            ->routes('dialogs/blocks.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\SiteType')
            ->setPrefix('/ccm/system/dialogs/site_type')
            ->routes('dialogs/site_types.php')
        ;

        $system->buildGroup()->setRequirements(['identifier' => '[A-Za-z0-9-_/.]+'])->routes('rss.php');

        $system->buildGroup()->routes('attributes.php');

        $system->buildGroup()
            ->routes('search.php')
        ;

        $system->buildGroup()->routes('express.php');

        $system->buildGroup()->routes('marketplace.php');

        $system->buildGroup()->routes('permissions.php');

        $system->buildGroup()->routes('trees.php');

        $system->buildGroup()->routes('site.php');

        $system->buildGroup()->routes('boards.php');

        $system->buildGroup()->routes('block_types.php');

        $system->buildGroup()->routes('calendar.php');

        $system->buildGroup()->routes('misc.php');

        $system->buildGroup()
            ->setNamespace('Concrete\Controller\Backend\Dashboard')
            ->setPrefix('/ccm/system/backend/dashboard')
            ->routes('backend/dashboard.php')
        ;

        $system->buildGroup()
            ->setNamespace('Concrete\Controller\Backend\Page\Type')
            ->setPrefix('/ccm/system/page/type')
            ->routes('backend/page_types.php')
        ;   

        $system->buildGroup()->setNamespace('Concrete\Controller\Dialog\Workflow')
            ->setPrefix('/ccm/system/dialogs/workflow')
            ->routes('dialogs/workflows.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Workflow')
            ->setPrefix('/ccm/system/workflow')
            ->routes('workflow.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Frontend\Conversations')
            ->setPrefix('/ccm/frontend/conversations')
            ->routes('conversations.php')
        ;

        $system->buildGroup()->setNamespace('Concrete\Controller\Frontend')
            ->setPrefix('/ccm/frontend/multilingual')
            ->routes('multilingual.php')
        ;
    }
}
