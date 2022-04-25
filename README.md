test

# RequestTracingBundle

Symfony bundle for request tracing.

This bundle ships with a [Monolog] processor and a [Guzzle Middleware] for
logging and propagating request IDs.

## Installation

```
composer require request-tracing/request-tracing-bundle
```

If you are using [Symfony Flex] everything works out-of-the-box.
If not, you should add this bundle manually to your `config/bundles.php`.

By default, the bundle will look for a `X-Request-Id` HTTP request header and use its value if present.
You can configure the header name with the following config:

```yaml
# config/packages/request_tracing.yaml
request_tracing:
  header: my_custom_header_name
```

## Monolog

This bundle will add the request ID to the context of each log record created with Monolog.
Be sure to install the [Monolog bundle] first:

```
composer require symfony/monolog-bundle
```

I recommend formatting Monolog in JSON format for easier log processing in tools like [Datadog]:

```yaml
# config/packages/monolog.yaml
monolog:
  handlers:
    main:
      formatter: monolog.formatter.json
```

## Guzzle

If you use the [Guzzle] HTTP client in your application you can use the [Guzzle Middleware] provided by this
bundle to pass the request ID of the original HTTP request as a header to a subsequent HTTP request.
This way you will be able to correlate HTTP requests when analyzing (access) logs.

The middleware is available in the [Dependency Injection Container] by its FQCN.
Here's an example services configuration:

```yaml
services:
  GuzzleHttp\Client:
    arguments:
      - { handler: '@GuzzleHttp\HandlerStack' }

  GuzzleHttp\HandlerStack:
    factory: ['GuzzleHttp\HandlerStack', 'create']
    calls:
      - push: ['@RequestTracing\RequestTracingBundle\GuzzleHttp\RequestIdMiddleware']
```

[Guzzle]: https://docs.guzzlephp.org/en/stable/index.html
[Guzzle Middleware]: https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware
[Datadog]: https://www.datadoghq.com/
[Monolog]: https://github.com/Seldaek/monolog
[Monolog bundle]: https://symfony.com/doc/current/logging.html#monolog
[Symfony Flex]: https://github.com/symfony/flex
[Dependency Injection Container]: https://symfony.com/doc/current/components/dependency_injection.html
