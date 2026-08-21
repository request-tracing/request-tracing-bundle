<?php

declare(strict_types=1);

namespace RequestTracing\RequestTracingBundle;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use RequestTracing\RequestTracingBundle\GuzzleHttp\RequestIdStorage;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /** @return iterable<BundleInterface> */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new MonologBundle();
        yield new RequestTracingBundle();
    }

    protected function configureContainer(ContainerConfigurator $c): void
    {
        $c->extension('framework', [
            'secret' => '%env(string:APP_SECRET)%',
            'test' => 'test' === $this->environment,
        ]);

        $c->extension('monolog', [
            'handlers' => [
                'main' => [
                    'type' => 'test', // https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/TestHandler.php
                    'level' => 'info',
                ],
            ],
        ]);

        $c->services()
            ->set('my_guzzle_http_handler_stack', HandlerStack::class)
            ->factory([HandlerStack::class, 'create'])
            ->public();
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('home', '/')
            ->controller([$this, 'home'])
            ->methods(['GET'])
        ;

        $routes->add('sub_request', '/sub_request')
            ->controller([$this, 'subRequest'])
            ->methods(['GET'])
        ;
    }

    public function home(LoggerInterface $logger): Response
    {
        $logger->info('Hello from /');

        return new Response('👍', Response::HTTP_OK);
    }

    public function subRequest(RequestIdStorage $requestIdStorage): Response
    {
        $handlerStack = $this->getContainer()->get('my_guzzle_http_handler_stack');

        if (!$handlerStack instanceof HandlerStack) {
            throw new \LogicException('The Guzzle handler stack is not configured.');
        }

        $client = new Client(['handler' => $handlerStack]);
        $client->request('GET', '/another_service');

        return new Response('👍', Response::HTTP_OK);
    }
}
