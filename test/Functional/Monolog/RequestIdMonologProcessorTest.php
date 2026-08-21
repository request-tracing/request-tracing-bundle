<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Functional\Monolog;

use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_adds_the_request_id_to_the_log_record_context(): void
    {
        $this->client->request('GET', '/', [], [], ['HTTP_X-Request-Id' => 'ea1379-42']);

        self::assertTrue($this->client->getResponse()->isOk());

        $testHandler = $this->logHandler();

        self::assertTrue($testHandler->hasRecordThatContains('Hello from /', Level::Info));
        self::assertTrue($testHandler->hasRecordThatPasses(static fn (LogRecord $record): bool => 'Hello from /' === $record->message && 'ea1379-42' === $record->extra['request_id'], Level::Info));
    }

    #[Test]
    public function it_does_not_add_the_request_id_to_the_log_record_context_when_the_request_id_header_is_not_present(): void
    {
        $this->client->request('GET', '/');

        self::assertTrue($this->client->getResponse()->isOk());

        $testHandler = $this->logHandler();

        self::assertTrue($testHandler->hasRecordThatContains('Hello from /', Level::Info));
        self::assertTrue($testHandler->hasRecordThatPasses(static fn (LogRecord $record): bool => 'Hello from /' === $record->message && !array_key_exists('request_id', $record->extra), Level::Info));
    }

    private function logHandler(): TestHandler
    {
        $logger = $this->client->getContainer()->get('logger');

        self::assertInstanceOf(Logger::class, $logger);

        $testHandler = $logger->getHandlers()[0];

        self::assertInstanceOf(TestHandler::class, $testHandler);

        return $testHandler;
    }
}
