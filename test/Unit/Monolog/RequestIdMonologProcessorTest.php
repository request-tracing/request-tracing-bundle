<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Unit\Monolog;

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

    public function setUp(): void
    {
        $this->processor = new RequestIdMonologProcessor($this->header);
    }

    /**
     * @test
     */
    public function it_adds_the_request_id_if_it_was_available_in_the_request()
    {
        $record = ['message' => 'w00t w00t'];
        $requestId = 'ea1379-42';
        $getResponseEvent = $this->createGetResponseEvent($requestId);

        $this->processor->onKernelRequest($getResponseEvent);

        $expectedRecord = $record;
        $expectedRecord['extra']['request_id'] = $requestId;

        $this->assertEquals($expectedRecord, $this->invokeProcessor($record));
    }

    /**
     * @test
     */
    public function it_leaves_the_record_untouched_if_no_request_id_was_available_in_the_request()
    {
        $record = ['message' => 'w00t w00t'];
        $getResponseEvent = $this->createGetResponseEvent();

        $this->processor->onKernelRequest($getResponseEvent);

        $expectedRecord = $record;

        $this->assertEquals($expectedRecord, $this->invokeProcessor($record));
    }

    /**
     * @test
     */
    public function it_leaves_the_record_untouched_if_no_request_was_handled()
    {
        $record = ['message' => 'w00t w00t'];

        $expectedRecord = $record;

        $this->assertEquals($expectedRecord, $this->invokeProcessor($record));
    }

    private function createGetResponseEvent($requestId = false)
    {
        $getResponseEventMock = $this->createMock(RequestEvent::class);

        $request = new Request();

        if (false !== $requestId) {
            $request->headers->set($this->header, $requestId);
        }

        $getResponseEventMock
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $getResponseEventMock;
    }

    private function invokeProcessor(array $record)
    {
        return call_user_func_array($this->processor, [$record]);
    }
}
