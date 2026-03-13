<?php

namespace Concrete\Core\Validation\CSRF;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\Session;

class SimpleToken
{
    public const DEFAULT_TOKEN_NAME = 'concrete_csrf_token';
    public const HEADER_NAME = 'X-CSRF-TOKEN';
    public const SESSION_TOKEN_NAME = 'ccm_session_token';

    /**
     * @var \Concrete\Core\Http\Request
     */
    protected $request;

    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    protected $config;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    public function __construct(Request $request, Repository $config, Session $session)
    {
        $this->request = $request;
        $this->config = $config;
        $this->session = $session;
    }

    public function shouldValidateRequest(?SymfonyRequest $request = null): bool
    {
        $request = $request ?: $this->request;

        return !in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    public function hasAuthenticatedSessionCookie(?SymfonyRequest $request = null): bool
    {
        $request = $request ?: $this->request;
        $loginCookie = sprintf('%s_LOGIN', $this->config->get('concrete.session.name'));
        $cookieValue = $request->cookies->get($loginCookie);

        return is_string($cookieValue) ? $cookieValue !== '' : !empty($cookieValue);
    }

    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getSessionToken(bool $createIfMissing = true): ?string
    {
        if (!$this->hasAuthenticatedSessionCookie()) {
            return null;
        }

        $token = $this->session->get(static::SESSION_TOKEN_NAME);
        if (is_string($token) && $token !== '') {
            return $token;
        }

        if (!$createIfMissing) {
            return null;
        }

        $token = $this->generate();
        $this->session->set(static::SESSION_TOKEN_NAME, $token);

        return $token;
    }

    public function getRequestToken(?SymfonyRequest $request = null): ?string
    {
        $request = $request ?: $this->request;

        $token = $request->headers->get(static::HEADER_NAME);
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = $request->request->get(static::DEFAULT_TOKEN_NAME);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function isValid(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $sessionToken = $this->getSessionToken(false);

        return is_string($sessionToken) && $sessionToken !== '' && hash_equals($sessionToken, $token);
    }

    public function validateRequest(?SymfonyRequest $request = null): bool
    {
        $request = $request ?: $this->request;

        if (!$this->shouldValidateRequest($request)) {
            return true;
        }

        if (!$this->hasAuthenticatedSessionCookie($request)) {
            return false;
        }

        return $this->isValid($this->getRequestToken($request));
    }

    public function getErrorMessage(?SymfonyRequest $request = null): string
    {
        $request = $request ?: $this->request;

        if ($request->headers->has(static::HEADER_NAME) || $request->isXmlHttpRequest()) {
            return t('Invalid token. Please reload the page and retry.');
        }

        return t('Invalid form token. Please reload this form and submit again.');
    }
}
