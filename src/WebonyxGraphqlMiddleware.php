<?php

namespace PsCs\Psr7\Middleware\Graphql;


use Psr\Http\Message\{
  ServerRequestInterface as Request,
  ResponseInterface as Response
};
use Zend\Diactoros\Response\JsonResponse;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use GraphQL\Server\StandardServer;

class WebonyxGraphqlMiddleware implements MiddlewareInterface {
  protected $handler = null;
  protected $graphqlUri = "";
  /**
   * @var array 
   */
  private $graphqlHeaderList = [
      "application/graphql"
  ];

  /**
   * @var array 
   */
  private $allowedMethods = [
      "GET", "POST"
  ];

  public function __construct(StandardServer $handler, $graphqlUri = '/graphql') {
    $this->handler = $handler;
    $this->graphqlUri = $graphqlUri;
  }

    public function process(Request $request, DelegateInterface $delegate)
    {
      if (!$this->isGraphqlRequest($request)) {
        return $delegate->process($request);
      }
     if (strtoupper($request->getMethod()) == "GET") {
        $params = $request->getQueryParams();
        $params["variables"] = empty($params["variables"]) ? null : $params["variables"];
        $request = $request->withQueryParams($params);
      } else {
         $params = $request->getParsedBody();
         $params["variables"] = empty($params["variables"]) ? null : $params["variables"];
         $request = $request->withParsedBody($params);
      }
      return new JsonResponse($this->handler->executePsrRequest($request));
    }

    private function isGraphqlRequest(Request $request)
    {
        return $this->hasUri($request) || $this->hasGraphQLHeader($request);
    }

    private function hasUri(Request $request)
    {
        return  $this->graphqlUri === $request->getUri()->getPath();
    }

    private function hasGraphQLHeader(Request $request)
    {
        if (!$request->hasHeader('content-type')) {
            return false;
        }

        $requestHeaderList = array_map(function($header){
            return trim($header);
        }, explode(",", $request->getHeaderLine("content-type")));

        foreach ($this->graphqlHeaderList as $allowedHeader) {
            if (in_array($allowedHeader, $requestHeaderList)){
                return true;
            }
        }

        return  false;
    }

}
