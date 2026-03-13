<?php

namespace Concrete\Tests\Validation\CSRF;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\Request;
use Concrete\Core\Validation\CSRF\SimpleToken;
use Concrete\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SimpleTokenTest extends TestCase
{
    protected function getToken(Request $request, Session $session): SimpleToken
    {
        $config = new Repository([
            'concrete' => [
                'session' => [
                    'name' => 'concrete_test',
                ],
            ],
        ]);

        return new SimpleToken($request, $config, $session);
    }

    public function testSessionTokenIsNotCreatedWithoutAuthenticatedCookie()
    {
        $request = Request::create('http://www.example.com/');
        $session = new Session(new MockArraySessionStorage());
        $token = $this->getToken($request, $session);

        $this->assertNull($token->getSessionToken());
        $this->assertFalse($session->has(SimpleToken::SESSION_TOKEN_NAME));
    }

    public function testSessionTokenIsCreatedAndReusedForAuthenticatedCookie()
    {
        $request = Request::create('http://www.example.com/', 'GET', [], ['concrete_test_LOGIN' => 'cookie-value']);
        $session = new Session(new MockArraySessionStorage());
        $token = $this->getToken($request, $session);

        $first = $token->getSessionToken();
        $second = $token->getSessionToken();

        $this->assertIsString($first);
        $this->assertSame($first, $second);
        $this->assertSame($first, $session->get(SimpleToken::SESSION_TOKEN_NAME));
    }

    public function testHeaderTokenTakesPrecedenceOverFormToken()
    {
        $request = Request::create(
            'http://www.example.com/',
            'POST',
            [SimpleToken::DEFAULT_TOKEN_NAME => 'form-token'],
            ['concrete_test_LOGIN' => 'cookie-value']
        );
        $request->headers->set(SimpleToken::HEADER_NAME, 'header-token');
        $session = new Session(new MockArraySessionStorage());
        $token = $this->getToken($request, $session);

        $this->assertSame('header-token', $token->getRequestToken($request));
    }
}
