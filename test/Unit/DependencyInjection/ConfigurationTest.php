<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Monolog\Test\TestCase;
use RequestTracing\RequestTracingBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    /** @test */
    public function it_provides_a_default_header_name(): void
    {
        $this->assertProcessedConfigurationEquals(
            [[]],
            ['header' => 'X-Request-Id']
        );
    }

    /** @test */
    public function it_allows_the_header_name_to_be_configured(): void
    {
        $this->assertProcessedConfigurationEquals(
            [['header' => 'X-Amzn-Trace-Id']],
            ['header' => 'X-Amzn-Trace-Id']
        );
    }
}
