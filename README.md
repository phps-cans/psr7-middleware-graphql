# [PSR 15](https://github.com/http-interop/http-middleware) compilant middleware to handle graphql 
This package is currently under development.
This package use the [official package of graphql's integration](https://github.com/webonyx/graphql-php) to handle Graphql Request.

This middleware is executed if the `content-type` of the request is `application/graphql` or if the configured URL is reached (by default it is /graphql)
This middleware use the [`StandardServer`](http://webonyx.github.io/graphql-php/executing-queries/#using-server) to handle request. It is free to you to create the schema and the server.
This middleware expect that JSON has already been decoded (by example with [`Psr7Middlewares\Middleware\Payload`](https://github.com/oscarotero/psr7-middlewares/blob/master/src/Middleware/Payload.php))

## Easy use:

To be able to use this package easily we recommend you to use:
- [ServiceProvider](https://github.com/container-interop/service-provider)
- [stratigility-harmony](https://github.com/thecodingmachine/stratigility-harmony)
- [Middleware List](https://github.com/thecodingmachine/middleware-list-universal-module)
- [Universal payload](https://github.com/phps-cans/psr7-middlewares-payload-universal-module)
- [Graphql Tools](https://github.com/phps-cans/harmony-graphql-tool)

This way, the zend-stratigility server is ready to use, JSON body are automatically parsed, middleware are piped, the StandardServer and Schema are automatically created.
## Using ServiceProvider

We recommend to use [stratigility-harmony](https://github.com/thecodingmachine/stratigility-harmony) to automatically configure your stratigility's server.

This package provide a [ServiceProvider](https://github.com/container-interop/service-provider) by default (src/ServiceProvider/DefaultServiceProvider.php). It expect the `StandardServer` to be registered in the container under the name `GraphQL\Server\StandardServer`. If you use [Middleware List](https://github.com/thecodingmachine/middleware-list-universal-module), it update the queue using the constant `MiddlewareOrder::ROUTER_EARLY`.


## using any http-interop compilant Middleware pipe

This example is based on [zend-stratigility](https://github.com/zendframework/zend-stratigility) middleware pipe:

```php

use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;
use PsCs\Psr7\Middleware\Graphql\WebonyxGraphqlMiddleware;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use Psr7Middlewares\Middleware\Payload;
use Zend\Stratigility\Middleware\NotFoundHandler;
use Zend\Diactoros\Response;
use Zend\Stratigility\NoopFinalHandler;

// Create fields
$field = FieldDefinition::create([
            "name" => "billPerYear",
            "type" => Type::string(),
            'args'    => [
                'id' => Type::nonNull(Type::id())
            ],
            "resolve" => function($rootValue, $args) {
                return "success on ".$args["id"];
            }

        ]);
//create the schema
$schema = new Schema([
            "query" => new ObjectType([
                'name'   => 'Query',
                'fields' => [
                    $field
                ]
            ])
        ]);
// create the standardServer of webonyx
$standardServer = new StandardServer(["schema" => $schema]);
// let instantiate our php server
$pipe = new MiddlewarePipe();
// Register the middleware which decode JSON body
$pipe->pipe(new \Psr7Middlewares\Middleware\Payload());
// Instantiate and register our middleware
$pipe->pipe(new WebonyxGraphqlMiddleware($standardServer));
// Add the notFoundHandler
$pipe->pipe(new NotFoundHandler(new Response()));
// Instantiate our server
$server = Server::createServer($pipe, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
// let tell to the server that we are ready
$server->listen(new NoopFinalHandler());
```

Feel free to report any issues.

## TODO

 - Write unit testing

