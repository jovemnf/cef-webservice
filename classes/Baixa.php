<?php

namespace CaixaWebService\classes;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use \CaixaWebService\service\Webservice;

class Baixa extends Base
{

    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Exception
     */
    function post (Request $request, Response $response, array $args) {
        try {

            $parsedBody = $request->getParsedBody();

            $res = $this->checkHeader($parsedBody, $response);

            if ($res) {
                return $res;
            }

            $ws = new Webservice($parsedBody['HEADER']);

            $arr = $ws->Baixar();

            if ($arr->getCodigoRetorno() !== 0) {
                return $this->getErros($response, $arr->getMensagemRetorno(), $arr->getArray());
            }

            if ($arr->getCodigoRetornoEmCodigoNegociavel() > 0) {
                return $this->getErros($response, $arr->getMensagemRetornoEmCodigoNegociavel(), $arr->getArray());
            }

            $aux = explode('=',  $arr->getMensagemRetorno());

            $status = trim($aux[1]);

            return $response->withJson(array(
                "status" => 200,
                "text" => $status,
                "returned" => $arr->getArray()
            ));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    function getErros (Response $response, $title, array $array) {

        $arr = array(
            "status" => 402,
            "error" => $title,
            "returned" => $array
        );

        return $response
            ->withStatus(402)
            ->withHeader('Content-Type', 'application/json')
            ->withJson($arr);

    }

}