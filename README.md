# [PSR 15](https://github.com/http-interop/http-middleware) compliant middleware to handle graphql 

This package uses the [webonyx/graphql-php](https://github.com/webonyx/graphql-php) to handle GraphQL requests.

This middleware is executed if the `content-type` of the request is `application/graphql` or if the configured URL is reached (by default it is `/graphql`).
This middleware consumes a [`StandardServer`](http://webonyx.github.io/graphql-php/executing-queries/#using-server) to handle the request.
This middleware expects that JSON has already been decoded (for instance with [`Psr7Middlewares\Middleware\Payload`](https://github.com/oscarotero/psr7-middlewares/blob/master/src/Middleware/Payload.php))

## Sample usage using any PSR-15 compliant Middleware pipe

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
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;


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
$defaultUri = '/graphql'; 
$debug = false;
// create the standardServer of webonyx
$standardServer = new StandardServer(["schema" => $schema]);
// let's instantiate our php server
$pipe = new MiddlewarePipe();
// Register the middleware which decode JSON body
$pipe->pipe(new \Psr7Middlewares\Middleware\Payload());
/* Instantiate and register our middleware
Params are:
- $standardServer : Webonyx's graphql server: [`StandardServer`](http://webonyx.github.io/graphql-php/executing-queries/#using-server)
- $responseFactory: A PSR-17 response factory
- $streamFactory: A PSR-17 stream factory
- $defaultUri = This middleware will be executed for each request matching the default URI and for each request having the content-type set to "application/graphql"
- $debug = IF false, minimal error will be reported (as specified in [handling error](http://webonyx.github.io/graphql-php/error-handling/). The value of $debug must be the same as specified in [`$debug`](http://webonyx.github.io/graphql-php/error-handling/#debugging-tools)
**/
$pipe->pipe(new WebonyxGraphqlMiddleware($standardServer, new ResponseFactory(), new StreamFactory(), $defaultUri, $debug)); 
// Add the notFoundHandler
$pipe->pipe(new NotFoundHandler(new Response()));
// Instantiate our server
$server = Server::createServer($pipe, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
// let tell to the server that we are ready
$server->listen(new NoopFinalHandler());
```

Feel free to report any issues.
