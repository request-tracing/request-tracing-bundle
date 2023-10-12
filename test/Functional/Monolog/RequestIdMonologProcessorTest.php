<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Functional\Monolog;

use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RequestIdMonologProcessorTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /** @test */
    public function it_adds_the_request_id_to_the_log_record_context(): void
    {
        $this->client->request('GET', '/', [], [], ['HTTP_X-Request-Id' => 'ea1379-42']);

        $this->assertTrue($this->client->getResponse()->isOk());

        $testHandler = $this->client->getContainer()->get('logger')->getHandlers()[0];

        $this->assertTrue($testHandler->hasRecordThatContains('Hello from /', Level::Info));
        $this->assertTrue($testHandler->hasRecordThatPasses(static fn (LogRecord $record): bool => 'Hello from /' === $record->message && 'ea1379-42' === $record->extra['request_id'], Level::Info));
    }

    /** @test */
    public function it_does_not_add_the_request_id_to_the_log_record_context_when_the_request_id_header_is_not_present(): void
    {
        $this->client->request('GET', '/');

        $this->assertTrue($this->client->getResponse()->isOk());

        $testHandler = $this->client->getContainer()->get('logger')->getHandlers()[0];

        $this->assertTrue($testHandler->hasRecordThatContains('Hello from /', Level::Info));
        $this->assertTrue($testHandler->hasRecordThatPasses(static fn (LogRecord $record): bool => 'Hello from /' === $record->message && !array_key_exists('request_id', $record->extra), Level::Info));
    }
}
