<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Unit\Monolog;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RequestTracing\RequestTracingBundle\Monolog\RequestIdMonologProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * this class is based on https://github.com/qandidate-labs/stack-request-id/blob/master/test/Qandidate/Stack/RequestId/MonologProcessorTest.php.
 */
final class RequestIdMonologProcessorTest extends TestCase
{
    private RequestIdMonologProcessor $processor;

    private string $header = 'Foo-Id';

    protected function setUp(): void
    {
        $this->processor = new RequestIdMonologProcessor($this->header);
    }

    /**
     * @test
     */
    public function it_adds_the_request_id_if_it_was_available_in_the_request(): void
    {
        $record = $this->createRecord();
        $requestId = 'ea1379-42';
        $getResponseEvent = $this->createGetResponseEvent($requestId);

        $this->processor->onKernelRequest($getResponseEvent);

        $expectedRecord = $record;
        $expectedRecord->extra['request_id'] = $requestId;

        $this->assertEquals($expectedRecord, $this->invokeProcessor($record));
    }

    /**
     * @test
     */
    public function it_leaves_the_record_untouched_if_no_request_id_was_available_in_the_request(): void
    {
        $record = $this->createRecord();
        $getResponseEvent = $this->createGetResponseEvent('');

        $this->processor->onKernelRequest($getResponseEvent);

        $expectedRecord = $record;

        $this->assertEquals($expectedRecord, $this->invokeProcessor($record));
    }

    /**
     * @test
     */
    public function it_leaves_the_record_untouched_if_no_request_was_handled(): void
    {
        $record = $this->createRecord();

        $expectedRecord = $record;

        $this->assertEquals($expectedRecord, $this->invokeProcessor($record));
    }

    private function createGetResponseEvent(string $requestId = ''): MockObject&RequestEvent
    {
        $getResponseEventMock = $this->createMock(RequestEvent::class);

        $request = new Request();

        if ('' !== $requestId) {
            $request->headers->set($this->header, $requestId);
        }

        $getResponseEventMock
            ->method('getRequest')
            ->willReturn($request);

        return $getResponseEventMock;
    }

    private function invokeProcessor(LogRecord $record): LogRecord
    {
        return call_user_func($this->processor, $record);
    }

    private function createRecord(): LogRecord
    {
        return new LogRecord(new \DateTimeImmutable(), 'channel', Level::Info, 'w00t w00t');
    }
}
