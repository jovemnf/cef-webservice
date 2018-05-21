<?php

namespace CaixaWebService\service;


class ResponseBaixa extends ResponseBase
{

    function __construct(array $arr){
        $this->response = $arr;
    }

    function getCodigoDeBarras()        {
        return (isset($this->response['DADOS']['BAIXA_BOLETO']['CODIGO_BARRAS'])) ?
            $this->response['DADOS']['BAIXA_BOLETO']['CODIGO_BARRAS'] : null;
    }

}