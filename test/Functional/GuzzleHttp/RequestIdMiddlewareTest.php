<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Functional\GuzzleHttp;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use RequestTracing\RequestTracingBundle\GuzzleHttp\RequestIdMiddleware;
use RequestTracing\RequestTracingBundle\GuzzleHttp\RequestIdStorage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RequestIdMiddlewareTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /** @test */
    public function it_adds_the_request_id_as_a_header_to_http_requests(): void
    {
        $handlerStack = HandlerStack::create(new MockHandler([new GuzzleHttpResponse()]));
        $handlerStack->push(new RequestIdMiddleware($this->getContainer()->get(RequestIdStorage::class)));

        $container = [];
        $handlerStack->push(Middleware::history($container));

        $this->client->getContainer()->set('my_guzzle_http_handler_stack', $handlerStack);

        $this->client->request('GET', '/sub_request', [], [], ['HTTP_X-Request-Id' => 'ea1379-42']);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var Request $request */
        $request = $container[0]['request'];

        $this->assertEquals('ea1379-42', $request->getHeaderLine('X-Request-Id'));
    }

    /** @test */
    public function it_does_not_add_the_request_id_as_a_header_to_http_requests_when_the_request_id_header_is_not_present(): void
    {
        $handlerStack = HandlerStack::create(new MockHandler([new GuzzleHttpResponse()]));
        $handlerStack->push(new RequestIdMiddleware($this->getContainer()->get(RequestIdStorage::class)));

        $container = [];
        $handlerStack->push(Middleware::history($container));

        $this->client->getContainer()->set('my_guzzle_http_handler_stack', $handlerStack);

        $this->client->request('GET', '/sub_request');

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var Request $request */
        $request = $container[0]['request'];

        $this->assertFalse($request->hasHeader('X-Request-Id'));
    }
}
