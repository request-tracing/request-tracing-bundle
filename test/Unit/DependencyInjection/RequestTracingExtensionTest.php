<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\Attributes\Test;
use RequestTracing\RequestTracingBundle\DependencyInjection\RequestTracingExtension;
use RequestTracing\RequestTracingBundle\Monolog\RequestIdMonologProcessor;

final class RequestTracingExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new RequestTracingExtension()];
    }

    #[Test]
    public function it_loads_the_extension_with_empty_config(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(RequestIdMonologProcessor::class, 0, 'X-Request-Id');
    }

    #[Test]
    public function it_loads_the_extension_with_a_custom_header_name(): void
    {
        $this->load([
            'header' => 'X-Amzn-Trace-Id',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(RequestIdMonologProcessor::class, 0, 'X-Amzn-Trace-Id');
    }
}
