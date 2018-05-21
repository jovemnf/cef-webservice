<?php

namespace CaixaWebService\classes;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Home
{

    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function index (Request $request, Response $response, array $args) {
        $data = array('text' => "API de configuração com a caixa");
        return $response->withJson($data);
    }

    public function getToken (Request $request, Response $response, array $args) {
        $data = array('text' => "API de configuração com a caixa");
        return $response->withJson($data);
    }

}