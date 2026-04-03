<?php

namespace Concrete\Core\Http\Middleware;

use Concrete\Core\Application\Service\Dashboard;
use Symfony\Component\HttpFoundation\Request;

class ValidateDashboardSimpleCsrfTokenMiddleware implements MiddlewareInterface
{
    /**
     * @var \Concrete\Core\Http\Middleware\ValidateSimpleCsrfTokenMiddleware
     */
    protected $middleware;

    /**
     * @var \Concrete\Core\Application\Service\Dashboard
     */
    protected $dashboard;

    public function __construct(ValidateSimpleCsrfTokenMiddleware $middleware, Dashboard $dashboard)
    {
        $this->middleware = $middleware;
        $this->dashboard = $dashboard;
    }

    public function process(Request $request, DelegateInterface $frame)
    {
        if (!$this->dashboard->inDashboard($request->getPathInfo())) {
            return $frame->next($request);
        }

        return $this->middleware->process($request, $frame);
    }
}
