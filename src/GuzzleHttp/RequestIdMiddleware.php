<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\GuzzleHttp;

use Psr\Http\Message\RequestInterface;

final class RequestIdMiddleware
{
    private RequestIdStorage $requestIdStorage;

    public function __construct(RequestIdStorage $requestIdStorage)
    {
        $this->requestIdStorage = $requestIdStorage;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($this->requestIdStorage->getRequestId()) {
                $request = $request->withHeader($this->requestIdStorage->getHeader(), $this->requestIdStorage->getRequestId());
            }

            return $handler($request, $options);
        };
    }
}
