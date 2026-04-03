<?php

namespace Concrete\Core\Http\Middleware;

use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Validation\CSRF\SimpleToken;
use http\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSimpleCsrfTokenMiddleware implements MiddlewareInterface
{
    /**
     * @var \Concrete\Core\Validation\CSRF\SimpleToken
     */
    protected $token;

    /**
     * @var \Concrete\Core\Http\ResponseFactoryInterface
     */
    protected $responseFactory;

    public function __construct(SimpleToken $token, ResponseFactoryInterface $responseFactory)
    {
        $this->token = $token;
        $this->responseFactory = $responseFactory;
    }

    public function process(Request $request, DelegateInterface $frame)
    {
        if (!$this->token->shouldValidateRequest($request)) {
            return $frame->next($request);
        }

        $requestToken = $this->token->getRequestToken($request);
        if ($this->token->isValid($requestToken)) {
            return $frame->next($request);
        }

        $message = $this->token->getErrorMessage($request);

        if (
            $request->headers->has(SimpleToken::HEADER_NAME)
            || $request->isXmlHttpRequest()
            || strpos((string) $request->headers->get('Accept'), 'json') !== false
        ) {
            return $this->responseFactory->json([
                'error' => $message,
                'errors' => [$message],
            ], Response::HTTP_FORBIDDEN);
        }

        throw new \RuntimeException($message);
    }
}
