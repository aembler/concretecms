<?php

namespace Concrete\Core\Authentication\Type\Facebook\Factory;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\Request;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use League\Url\UrlInterface;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\SymfonySession;
use OAuth\OAuth2\Service\Facebook;
use OAuth\ServiceFactory;
use Symfony\Component\HttpFoundation\Session\Session;

class FacebookServiceFactory implements ApplicationAwareInterface
{

    use ApplicationAwareTrait;

    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    protected $config;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @var \Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface
     */
    protected $urlResolver;

    /**
     * @var \Concrete\Core\Http\Request
     */
    protected $request;

    /**
     * CommunityServiceFactory constructor.
     * @param \Concrete\Core\Config\Repository\Repository $config
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $url
     */
    public function __construct(Repository $config, Session $session, ResolverManagerInterface $url, Request $request)
    {
        $this->config = $config;
        $this->session = $session;
        $this->urlResolver = $url;
        $this->request = $request;
    }

    /**
     * Create a service object given a ServiceFactory object
     *
     * @return \OAuth\Common\Service\ServiceInterface
     */
    public function createService(UrlInterface $callbackUrl = null)
    {
        $appId = $this->config->get('auth.facebook.appid');
        $appSecret = $this->config->get('auth.facebook.secret');

        /** @var ServiceFactory $factory */
        $factory = $this->app->make('oauth/factory/service');

        // Get the callback url
        if (!$callbackUrl) {
            $callbackUrl = $this->urlResolver->resolve(['/ccm/system/authentication/oauth2/facebook/callback/']);
        }
        if ($callbackUrl->getHost() == '') {
            $callbackUrl = $callbackUrl->setHost($this->request->getHost());
            $callbackUrl = $callbackUrl->setScheme($this->request->getScheme());
        }

        // Create a credential object with our ID, Secret, and callback url
        $credentials = new Credentials($appId, $appSecret, (string) $callbackUrl);

        // Create a new session storage object and pass it the active session
        $storage = new SymfonySession($this->session, false);

        // Create the service using the oauth service factory
        return $factory->createService('facebook', $credentials, $storage, [Facebook::SCOPE_EMAIL]);
    }

}
