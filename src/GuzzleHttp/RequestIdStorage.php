<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\GuzzleHttp;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestIdStorage
{
    private string $header = '';
    private string $requestId = '';

    public function __construct(string $header = 'X-Request-Id')
    {
        $this->header = $header;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->requestId = $event->getRequest()->headers->get($this->header, '');
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getHeader(): string
    {
        return $this->header;
    }
}
