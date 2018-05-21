<?php

namespace CaixaWebService\classes;

use Slim\Http\Response;

abstract class Base
{

    /**
     * @param array $parsedBody
     * @param Response $response
     * @return bool|Response
     */
    protected function checkHeader (array $parsedBody, Response $response) {
        if (!is_array($parsedBody) OR ! is_array($parsedBody['HEADER']) OR ! $parsedBody['HEADER']) {
            return $this->getErros($response, "HEADER não informado ou incorreto", array());
        }

        if (! $parsedBody['HEADER']['CODIGO_BENEFICIARIO']) {
            return $this->getErros($response, "CODIGO_BENEFICIARIO não informado", $parsedBody);
        }

        if (! $parsedBody['HEADER']['NOSSO_NUMERO']) {
            return $this->getErros($response, "NOSSO_NUMERO não informado", $parsedBody);
        }

        if (strlen($parsedBody['HEADER']['NOSSO_NUMERO']) !== 17) {
            return $this->getErros($response, "NOSSO_NUMERO com qtde incorreta de caracteres", $parsedBody);
        }

        if (! $parsedBody['HEADER']['UNIDADE']) {
            return $this->getErros($response, "UNIDADE não informado", $parsedBody);
        }

        if (! $parsedBody['HEADER']['CNPJ']) {
            return $this->getErros($response, "CNPJ não informado", $parsedBody);
        }

        return false;
    }

    /**
     * @param Response $response
     * @param $title
     * @param array $array
     * @return Response
     */
    protected function getErros (Response $response, $title, array $array) {

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