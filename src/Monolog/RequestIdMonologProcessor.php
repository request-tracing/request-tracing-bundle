<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Monolog;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * this class is based on https://github.com/qandidate-labs/stack-request-id/blob/master/src/Qandidate/Stack/RequestId/MonologProcessor.php.
 */
final class RequestIdMonologProcessor implements ProcessorInterface
{
    private string $requestId = '';

    public function __construct(private readonly string $header = 'X-Request-Id')
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->requestId = $event->getRequest()->headers->get($this->header, '');
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ('' !== $this->requestId) {
            $record->extra['request_id'] = $this->requestId;
        }

        return $record;
    }
}
