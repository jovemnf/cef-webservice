<?php

namespace CaixaWebService\classes;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use \CaixaWebService\service\Webservice;

class Insert extends Base
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
    function post (Request $request, Response $response) {
        try {

            $parsedBody = $request->getParsedBody();

            $res = $this->checkHeader($parsedBody, $response);

            if ($res) {
                return $res;
            }

            if (! $parsedBody['DADOS']['NUMERO_DOCUMENTO']) {
                return $this->getErros($response, "NOSSO_NUMERO não informado", $parsedBody);
            }

            if (strlen($parsedBody['DADOS']['NOSSO_NUMERO']) !== 17) {
                return $this->getErros($response, "NOSSO_NUMERO com qtde incorreta de caracteres", $parsedBody);
            }

            if (! $parsedBody['DADOS']['NUMERO_DOCUMENTO']) {
                return $this->getErros($response, "NUMERO_DOCUMENTO não informado", $parsedBody);
            }

            if (strlen($parsedBody['DADOS']['NUMERO_DOCUMENTO']) !== 11) {
                return $this->getErros($response, "NUMERO_DOCUMENTO com qtde incorreta de caracteres", $parsedBody);
            }

            if (! $parsedBody['DADOS']['VALOR']) {
                return $this->getErros($response, "VALOR não informado", $parsedBody);
            }

            if (! $parsedBody['DADOS']['DATA_EMISSAO']) {
                return $this->getErros($response, "DATA_EMISSAO não informado", $parsedBody);
            }

            if (! $parsedBody['DADOS']['DATA_VENCIMENTO']) {
                return $this->getErros($response, "DATA_VENCIMENTO não informado", $parsedBody);
            }

            if (! $parsedBody['DADOS']['DATA_VENCIMENTO']) {
                return $this->getErros($response, "DATA_VENCIMENTO não informado", $parsedBody);
            }

            if (! isset($parsedBody['DADOS']['PAGADOR'])) {
                return $this->getErros($response, "PAGADOR não informado", $parsedBody);
            }

            if (! isset($parsedBody['DADOS']['PAGADOR']['CPF']) and ! isset($parsedBody['DADOS']['PAGADOR']['CNPJ'])) {
                return $this->getErros($response, "PAGADOR => CPF ou CNPJ não informado", $parsedBody);
            }

            if (! isset($parsedBody['DADOS']['PAGADOR']['NOME']) and ! isset($parsedBody['DADOS']['PAGADOR']['RAZAO_SOCIAL'])) {
                return $this->getErros($response, "PAGADOR => NOME ou RAZAO_SOCIAL não informado", $parsedBody);
            }

            if (! isset($parsedBody['DADOS']['PAGADOR']['ENDERECO'])) {
                return $this->getErros($response, "PAGADOR => ENDERECO não informado", $parsedBody);
            }

            $endereco = $parsedBody['DADOS']['PAGADOR']['ENDERECO'];

            if (
                ! isset($endereco['LOGRADOURO']) or
                ! isset($endereco['BAIRRO']) or
                ! isset($endereco['CIDADE']) or
                ! isset($endereco['UF']) or
                ! isset($endereco['CEP'])) {
                return $this->getErros($response, "PAGADOR => ENDERECO não informado", $parsedBody);
            }

            if (! $parsedBody['DADOS']['POS_VENCIMENTO']) {
                return $this->getErros($response, "POS_VENCIMENTO não informado", $parsedBody);
            }

            if (! $parsedBody['DADOS']['TIPO_ESPECIE']) {
                $parsedBody['DADOS']['TIPO_ESPECIE'] = '04';
            }

            if (! $parsedBody['DADOS']['FLAG_ACEITE']) {
                $parsedBody['DADOS']['FLAG_ACEITE'] = 'S';
            }

            if (! $parsedBody['DADOS']['VALOR_ABATIMENTO']) {
                $parsedBody['DADOS']['VALOR_ABATIMENTO'] = '0';
            }

            if (! $parsedBody['DADOS']['JUROS_MORA']) {
                $parsedBody['DADOS']['JUROS_MORA'] = array(
                    'TIPO' => 'ISENTO',
                    'VALOR' => '0',
                );
            }

            if (! $parsedBody['DADOS']['PAGAMENTO']) {
                $parsedBody['DADOS']['PAGAMENTO'] = array(
                    'TIPO' => 'NAO_ACEITA_VALOR_DIVERGENTE',
                    'QUANTIDADE_PERMITIDA' => '01',
                    'VALOR_MINIMO' => '0.00',
                    'VALOR_MAXIMO' => '0.00'
                );
            }

            if (! $parsedBody['DADOS']['POS_VENCIMENTO']['NUMERO_DIAS']) {
                $parsedBody['DADOS']['POS_VENCIMENTO']['NUMERO_DIAS'] = 120;
            }

            if (! $parsedBody['DADOS']['POS_VENCIMENTO']['ACAO']) {
                $parsedBody['DADOS']['POS_VENCIMENTO']['ACAO'] = "DEVOLVER";
            }

            if ($parsedBody['DADOS']['POS_VENCIMENTO']['ACAO'] != 'DEVOLVER' and $parsedBody['DADOS']['POS_VENCIMENTO']['ACAO'] != 'PROTESTAR') {
                return $this->getErros($response, "POS_VENCIMENTO => ACAO informado está incorreto", $parsedBody);
            }

            $ws = new Webservice($parsedBody['HEADER']);

            $arr = $ws->Insert($parsedBody['DADOS']);

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

}