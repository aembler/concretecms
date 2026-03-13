<?php

namespace Concrete\Tests\Http\Middleware;

use Concrete\Core\Http\Middleware\DelegateInterface;
use Concrete\Core\Http\Middleware\ValidateSimpleCsrfTokenMiddleware;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Validation\CSRF\SimpleToken;
use Concrete\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSimpleCsrfTokenMiddlewareTest extends TestCase
{
    public function testSafeRequestsPassThrough()
    {
        $token = $this->createMock(SimpleToken::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $delegate = $this->createMock(DelegateInterface::class);
        $request = Request::create('http://www.example.com/', 'GET');
        $response = new Response('ok');

        $token->expects($this->once())
            ->method('shouldValidateRequest')
            ->with($request)
            ->willReturn(false);
        $delegate->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response);

        $middleware = new ValidateSimpleCsrfTokenMiddleware($token, $responseFactory);

        $this->assertSame($response, $middleware->process($request, $delegate));
    }

    public function testValidUnsafeRequestsPassThrough()
    {
        $token = $this->createMock(SimpleToken::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $delegate = $this->createMock(DelegateInterface::class);
        $request = Request::create('http://www.example.com/', 'POST');
        $response = new Response('ok');

        $token->expects($this->once())
            ->method('shouldValidateRequest')
            ->with($request)
            ->willReturn(true);
        $token->expects($this->once())
            ->method('getRequestToken')
            ->with($request)
            ->willReturn('valid-token');
        $token->expects($this->once())
            ->method('isValid')
            ->with('valid-token')
            ->willReturn(true);
        $delegate->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response);

        $middleware = new ValidateSimpleCsrfTokenMiddleware($token, $responseFactory);

        $this->assertSame($response, $middleware->process($request, $delegate));
    }

    public function testInvalidUnsafeRequestsReturnForbiddenResponse()
    {
        $token = $this->createMock(SimpleToken::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $delegate = $this->createMock(DelegateInterface::class);
        $request = Request::create('http://www.example.com/', 'POST');
        $response = new Response('invalid', Response::HTTP_FORBIDDEN);

        $token->expects($this->once())
            ->method('shouldValidateRequest')
            ->with($request)
            ->willReturn(true);
        $token->expects($this->once())
            ->method('getRequestToken')
            ->with($request)
            ->willReturn(null);
        $token->expects($this->once())
            ->method('isValid')
            ->with(null)
            ->willReturn(false);
        $token->expects($this->once())
            ->method('getErrorMessage')
            ->with($request)
            ->willReturn('Invalid form token.');
        $responseFactory->expects($this->once())
            ->method('create')
            ->with('Invalid form token.', Response::HTTP_FORBIDDEN)
            ->willReturn($response);
        $delegate->expects($this->never())->method('next');

        $middleware = new ValidateSimpleCsrfTokenMiddleware($token, $responseFactory);

        $this->assertSame($response, $middleware->process($request, $delegate));
    }
}
