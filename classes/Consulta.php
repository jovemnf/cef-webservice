<?php

namespace CaixaWebService\classes;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use \CaixaWebService\service\Webservice;

class Consulta extends Base
{

    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Exception
     */
    function post (Request $request, Response $response) {
        try {

            $parsedBody = $request->getParsedBody();

            $res = $this->checkHeader($parsedBody, $response);

            if ($res) {
                return $res;
            }

            $ws = new Webservice($parsedBody['HEADER']);

            $arr = $ws->Consultar();

            if ($arr->getCodigoRetorno() !== 0) {
                return $this->erros($response, $arr->getMensagemRetorno(), $arr->getArray(), $parsedBody);
            }

            if ($arr->getCodigoRetornoEmCodigoNegociavel() > 0) {
                return $this->erros($response, $arr->getMensagemRetornoEmCodigoNegociavel(), $arr->getArray(), $parsedBody);
            }

            $aux = explode('=',  $arr->getMensagemRetornoEmCodigoNegociavel());

            $status = trim($aux[1]);

            $paid = false;

            switch ($status) {
                case "LIQUIDADO":
                case "LIQUIDADO NO CARTORIO":
                case "TITULO JA PAGO NO DIA":
                case "BAIXADO POR DEVOLUCAO":
                    $paid = true;
                    break;
            }

            return $response->withJson(array(
                "status" => 200,
                "text" => $status,
                "paid" => $paid,
                "returned" => $arr->getArray()
            ));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    function erros (Response $response, $title, array $array, array $body) {

        $arr = array(
            "status" => 402,
            "error" => $title,
            "body" => $body,
            "returned" => $array
        );

        return $response
            ->withStatus(402)
            ->withHeader('Content-Type', 'application/json')
            ->withJson($arr);

    }

}