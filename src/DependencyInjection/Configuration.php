<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('request_tracing');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('header')
                    ->defaultValue('X-Request-Id')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
