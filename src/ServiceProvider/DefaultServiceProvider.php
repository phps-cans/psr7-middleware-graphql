<?php

namespace PsCs\Psr7\Middleware\Graphql\ServiceProvider;

use Interop\Container\ServiceProvider;
use GraphQL\Type\Schema;

use GraphQL\Type\Definition\ObjectType;

use Interop\Container\ContainerInterface as Container;
use Interop\Container\ServiceProviderInterface;
use TheCodingMachine\MiddlewareOrder;
use GraphQL\Server\StandardServer;
use PsCs\Psr7\Middleware\Graphql\WebonyxGraphqlMiddleware;

class DefaultServiceProvider implements ServiceProviderInterface {

    public static function getDebug(Container $container, callable $previous = null) {
        return false;
    }

    public static function getMiddleware(Container $container, callable $previous = null): WebonyxGraphqlMiddleware {
        $middleware = new WebonyxGraphqlMiddleware($container->get(StandardServer::class));
        $middleware->setDebug( $container->get(\GraphQL\Error\Debug::class));
        return $middleware;
    }

    public static function updatePriorityQueue(Container $container, callable $previous = null) : \SplPriorityQueue
    {
        if ($previous) {
            $priorityQueue = $previous();
            $priorityQueue->insert($container->get(WebonyxGraphqlMiddleware::class), MiddlewareOrder::ROUTER_EARLY);
            return $priorityQueue;
        } else {
            throw new \InvalidArgumentException("Could not find declaration for service '".MiddlewareListServiceProvider::MIDDLEWARES_QUEUE."'.");
        }
    }

    /**
     * Returns a list of all container entries registered by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the entry, aka the **factory**
     *
     * Factories have the following signature:
     *        function(\Psr\Container\ContainerInterface $container)
     *
     * @return callable[]
     */
    public function getFactories()
    {
        return [
            (WebonyxGraphqlMiddleware::class) => [self::class, 'getMiddleware'],
            \GraphQL\Error\Debug::class => [self::class, 'getDebug']
        ];
    }

    /**
     * Returns a list of all container entries extended by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the modified entry
     *
     * Callables have the following signature:
     *        function(Psr\Container\ContainerInterface $container, $previous)
     *     or function(Psr\Container\ContainerInterface $container, $previous = null)
     *
     * About factories parameters:
     *
     * - the container (instance of `Psr\Container\ContainerInterface`)
     * - the entry to be extended. If the entry to be extended does not exist and the parameter is nullable, `null` will be passed.
     *
     * @return callable[]
     */
    public function getExtensions()
    {
        return [\TheCodingMachine\MiddlewareListServiceProvider::MIDDLEWARES_QUEUE => [self::class, 'updatePriorityQueue']];
    }
}
