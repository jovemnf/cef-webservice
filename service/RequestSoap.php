<?php

namespace CaixaWebService\service;

define('RETRIES',  2);                 // número de tentativas de conexão com o WS antes de falhar
define('TIMEOUT',  5);                  // timeout para desistir da resposta
define('INTERVAL', 1.5);                // intervalo entre tentativas

trait RequestSoap
{

    /**
     * Encapsulamento da chamada do NuSOAP ao WebService
     *
     * Devido à instabilidade do serviço, faz consultas repetidas até o
     * número de tentativas definido em RETRIES. Deve ser usado ao invés do
     * método `nusoap_client->call` da biblioteca.
     * @param string $wsdl
     * @param string $operacao
     * @param string $conteudo
     * @return array
     * @throws \Exception
     */
    private function CallNuSOAP($wsdl, $operacao, $conteudo) {
        try {
            $client = new \nusoap_client($wsdl, $wsdl = true, $timeout = TIMEOUT);
            // @TODO implementar consulta com certificado
            $client->curl_options = array('insecure' => true);
            $done = false;
            $retries = 0;

            while (!$done) {
                if (++$retries > RETRIES)
                    $this->ExibeErro('INDISP');

                $response = @$client->call($operacao, $conteudo, $retries);
                $err = $client->getError();

                if (!$client->fault && !$err) {
                    $done = true;
                } else {
                    sleep(INTERVAL);
                }
            }

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Faz a chamada ao WebService verificando as mensagens de erro
     * documentadas no manual.
     *
     * Código de retorno '02' são erros indisponibilidade na ponta (Pág. 35)
     *
     * Demais códigos de retorno (Págs. 33 a 35) devem ser checados pela
     * rotina que invoca este método
     * @throws \Exception
     */
    private function Call($wsdl, $operacao, $conteudo) {
        try {
            $response = $this->CallNusoap($wsdl, $operacao, $conteudo);
            $codret = $response['COD_RETORNO'];

            // Código 0 = operação efetuada
            if (intval($codret) === 0)
                return $response;

            /* Erros próprios de sistema (Pág. 35) que acarretam em erros fatais
             *   - Código 02 = sistema indisponível
             *   - Código X5 = formatação de mensagem
             */
            $cod_erros = array('02', 'X5');
            if (isset($response['COD_RETORNO']) && in_array($response['COD_RETORNO'], $cod_erros)) {
                $this->ExibeErro('INDISP');
            }

            /* Erros de negócio (Págs. 33 a 35) que devem ser tratados pela
             * rotina que invoca esta chamada
             */
            if (isset($response['DADOS']['CONTROLE_NEGOCIAL']['MENSAGENS']['RETORNO'])) {
                if (preg_match('/\((.+)\).*/', $response['DADOS']['CONTROLE_NEGOCIAL']['MENSAGENS']['RETORNO'], $m)) {
                    $response['COD_RETORNO'] = $m[1];
                    $response['MSG_RETORNO'] = $response['DADOS']['CONTROLE_NEGOCIAL']['MENSAGENS']['RETORNO'];

                    return $response;
                }
            }

            $this->ExibeErro('INDISP');

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Formata uma mensagem de erro na tela
     *
     * @param string $txt Exibido ao usuário
     * @throws \Exception
     */
    private function ExibeErro($txt = '') {
        if ($txt == 'INDISP') {
            $txt = 'O sistema de boletos da Caixa Econômica Federal encontra-se indisponível. Tente acessar o link mais tarde.';
        } else if ($txt == '') {
            $txt = "Houve um erro ao gerar o boleto. Por favor, visite esta página mais tarde.";
        }
        throw new \Exception($txt);
    }

}