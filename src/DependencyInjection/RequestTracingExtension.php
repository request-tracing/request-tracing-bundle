<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\DependencyInjection;

use RequestTracing\RequestTracingBundle\Monolog\RequestIdMonologProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class RequestTracingExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new Configuration(), [$mergedConfig]);

        $definition = $container->getDefinition(RequestIdMonologProcessor::class);
        $definition->addArgument($config['header']);
    }
}
