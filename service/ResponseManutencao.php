<?php

namespace CaixaWebService\service;

class ResponseManutencao extends ResponseBase
{

    function __construct(array $arr){
        $this->response = $arr;
    }

    public function __toString()
    {
        return 'Ninety nine green bottles';
    }

    /**
     * Obtém o código de retorno com o status das respostas do webservice
     */
    function getCodigoRetorno() {
        if (isset($this->response['DADOS']['CONTROLE_NEGOCIAL']['COD_RETORNO']))
            return intval($this->response['DADOS']['CONTROLE_NEGOCIAL']['COD_RETORNO']);

        return null;
    }

    /**
     * Obtém url para impressão do boleto
     */
    function getUrlBoleto() {
        if (isset($this->response['DADOS']['ALTERA_BOLETO']['URL']))
            return $this->response['DADOS']['ALTERA_BOLETO']['URL'];

        return null;
    }

}