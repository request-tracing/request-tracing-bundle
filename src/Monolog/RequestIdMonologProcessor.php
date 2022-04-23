<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Monolog;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * this class is based on https://github.com/qandidate-labs/stack-request-id/blob/master/src/Qandidate/Stack/RequestId/MonologProcessor.php.
 */
final class RequestIdMonologProcessor
{
    private string $header = '';
    private string $requestId = '';

    public function __construct(string $header = 'X-Request-Id')
    {
        $this->header = $header;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->requestId = (string) $event->getRequest()->headers->get($this->header, '');
    }

    public function __invoke(array $record): array
    {
        if ($this->requestId) {
            $record['extra']['request_id'] = $this->requestId;
        }

        return $record;
    }
}
