<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Functional\GuzzleHttp;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RequestTracing\RequestTracingBundle\GuzzleHttp\RequestIdMiddleware;
use RequestTracing\RequestTracingBundle\GuzzleHttp\RequestIdStorage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RequestIdMiddlewareTest extends WebTestCase
{
    private KernelBrowser $client;

    private ?RequestInterface $sentRequest = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    #[Test]
    public function it_adds_the_request_id_as_a_header_to_http_requests(): void
    {
        $this->client->getContainer()->set('my_guzzle_http_handler_stack', $this->handlerStack());

        $this->client->request('GET', '/sub_request', [], [], ['HTTP_X-Request-Id' => 'ea1379-42']);

        self::assertTrue($this->client->getResponse()->isOk());
        self::assertSame('ea1379-42', $this->sentRequest()->getHeaderLine('X-Request-Id'));
    }

    #[Test]
    public function it_does_not_add_the_request_id_as_a_header_to_http_requests_when_the_request_id_header_is_not_present(): void
    {
        $this->client->getContainer()->set('my_guzzle_http_handler_stack', $this->handlerStack());

        $this->client->request('GET', '/sub_request');

        self::assertTrue($this->client->getResponse()->isOk());
        self::assertFalse($this->sentRequest()->hasHeader('X-Request-Id'));
    }

    /**
     * A handler stack that records the outgoing request so the test can assert on the headers the
     * middleware added, and that never actually leaves the process.
     *
     * @return HandlerStack<callable(RequestInterface, array<array-key, mixed>): PromiseInterface<ResponseInterface, mixed>>
     */
    private function handlerStack(): HandlerStack
    {
        $handlerStack = HandlerStack::create(new MockHandler([new GuzzleHttpResponse()]));
        $handlerStack->push(new RequestIdMiddleware($this->requestIdStorage()));
        $handlerStack->push(Middleware::tap(function (RequestInterface $request): void {
            $this->sentRequest = $request;
        }));

        return $handlerStack;
    }

    private function requestIdStorage(): RequestIdStorage
    {
        $requestIdStorage = self::getContainer()->get(RequestIdStorage::class);

        self::assertInstanceOf(RequestIdStorage::class, $requestIdStorage);

        return $requestIdStorage;
    }

    private function sentRequest(): RequestInterface
    {
        self::assertInstanceOf(RequestInterface::class, $this->sentRequest);

        return $this->sentRequest;
    }
}
