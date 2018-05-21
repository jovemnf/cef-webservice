<?php
/**
 * Created by PhpStorm.
 * User: wallacesilva
 * Date: 21/05/2018
 * Time: 09:53
 */

namespace CaixaWebService\service;


class ResponseUpdate extends ResponseBase
{

    function __construct(array $arr){
        $this->response = $arr;
    }

    function getCodigoDeBarras()        {
        return (isset($this->response['DADOS']['BAIXA_BOLETO']['CODIGO_BARRAS'])) ?
            $this->response['DADOS']['BAIXA_BOLETO']['CODIGO_BARRAS'] : null;
    }

}