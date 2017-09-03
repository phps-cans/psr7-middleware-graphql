<?php

namespace PsCs\Psr7\Middleware\Graphql\ServiceProvider;

use Interop\Container\ServiceProvider;
use GraphQL\Type\Schema;

use GraphQL\Type\Definition\ObjectType;

use Interop\Container\ContainerInterface as Container;
use TheCodingMachine\MiddlewareOrder;
use GraphQL\Server\StandardServer;
use PsCs\Psr7\Middleware\Graphql\WebonyxGraphqlMiddleware;

class DefaultServiceProvider implements ServiceProvider {
 public function getServices()
    {
        return [
            (WebonyxGraphqlMiddleware::class) => [self::class, 'getMiddleware'],
            \TheCodingMachine\MiddlewareListServiceProvider::MIDDLEWARES_QUEUE => [self::class, 'updatePriorityQueue'],
            \GraphQL\Error\Debug::class => [self::class, 'getDebug']
        ]; // By convention
    }

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
}