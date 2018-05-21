<?php
namespace CaixaWebService;

date_default_timezone_set('America/Sao_Paulo');

use CaixaWebService\classes\Baixa;
use CaixaWebService\classes\Consulta;
use CaixaWebService\classes\Home;
use Slim\Http\Request;
use Slim\Http\Response;
use Tuupola\Middleware\JwtAuthentication;
use Symfony\Component\Dotenv;

require "vendor/autoload.php";

$dotenv = new Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');

$c['phpErrorHandler'] = function ($c) {
    return function (Request $request, Response $response, $error) use ($c) {
        //echo $error;
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array("status" => 500, "error" => "Something went wrong!", "text" => $error->getMessage())));
    };
};

//Override the default Not Found Handler
$c['notFoundHandler'] = function ($c) {
    return function (Request $request, Response $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array("status" => 404, "error" => "Not found")));
    };
};

$c['notAllowedHandler'] = function ($c) {
    return function (Request $request, Response $response, $methods) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array("status" => 405, "error" => "Method not allowed")));
    };
};

try {
    $app = new \Slim\App($c);

    $app->options('/{routes:.+}', function (Request $request, Response $response) {
        return $response;
    });

    $app->add(function ($req, $res, $next) {
        $response = $next($req, $res);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });

    $app->add(new JwtAuthentication([
        "path" => ["/consulta"],
        "ignore" => ["/status"],
        "secret" => getenv('KEY_SECRET'),
        "error" => function ($response, $arguments) {
            $data = array();
            $data["status"] = 401;
            $data["error"] = $arguments["message"];
            return $response
                ->withJson($data);
        }
    ]));

    $app->post('/consultar[/{type}]', Consulta::class . ':post');
    $app->post('/baixar[/{type}]', Baixa::class . ':post');
    $app->get('/status', Home::class . ':index');
    $app->get('/token', Home::class . ':getToken');

    $app->run();
} catch (\Exception $e) {
    echo $e;
}