<?php

namespace Concrete\Tests\Http\Middleware;

use Concrete\Core\Application\Service\Dashboard;
use Concrete\Core\Http\Middleware\DelegateInterface;
use Concrete\Core\Http\Middleware\ValidateDashboardSimpleCsrfTokenMiddleware;
use Concrete\Core\Http\Middleware\ValidateSimpleCsrfTokenMiddleware;
use Concrete\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateDashboardSimpleCsrfTokenMiddlewareTest extends TestCase
{
    public function testNonDashboardRequestsPassThroughWithoutCsrfValidation()
    {
        $validator = $this->createMock(ValidateSimpleCsrfTokenMiddleware::class);
        $dashboard = $this->createMock(Dashboard::class);
        $delegate = $this->createMock(DelegateInterface::class);
        $request = Request::create('http://www.example.com/login', 'POST');
        $response = new Response('ok');

        $dashboard->expects($this->once())
            ->method('inDashboard')
            ->with('/login')
            ->willReturn(false);
        $validator->expects($this->never())
            ->method('process');
        $delegate->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response);

        $middleware = new ValidateDashboardSimpleCsrfTokenMiddleware($validator, $dashboard);

        $this->assertSame($response, $middleware->process($request, $delegate));
    }

    public function testDashboardRequestsAreDelegatedToTheCsrfValidator()
    {
        $validator = $this->createMock(ValidateSimpleCsrfTokenMiddleware::class);
        $dashboard = $this->createMock(Dashboard::class);
        $delegate = $this->createMock(DelegateInterface::class);
        $request = Request::create('http://www.example.com/dashboard/users/search', 'POST');
        $response = new Response('ok');

        $dashboard->expects($this->once())
            ->method('inDashboard')
            ->with('/dashboard/users/search')
            ->willReturn(true);
        $validator->expects($this->once())
            ->method('process')
            ->with($request, $delegate)
            ->willReturn($response);
        $delegate->expects($this->never())
            ->method('next');

        $middleware = new ValidateDashboardSimpleCsrfTokenMiddleware($validator, $dashboard);

        $this->assertSame($response, $middleware->process($request, $delegate));
    }
}
