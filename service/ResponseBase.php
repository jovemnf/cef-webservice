<?php

namespace CaixaWebService\service;


abstract class ResponseBase
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @return int
     */
    function getCodigoRetorno()    {
        return intval($this->response['COD_RETORNO']);
    }

    /**
     * @return int
     */
    function getCodigoRetornoEmCodigoNegociavel() {
        return (isset($this->response['DADOS']['CONTROLE_NEGOCIAL']['COD_RETORNO']))
            ? intval($this->response['DADOS']['CONTROLE_NEGOCIAL']['COD_RETORNO']) : null;
    }

    /**
     * @return string
     */
    function getMensagemRetornoEmCodigoNegociavel() {
        return (isset($this->response['DADOS']['CONTROLE_NEGOCIAL']['MENSAGENS']['RETORNO']))
            ? $this->response['DADOS']['CONTROLE_NEGOCIAL']['MENSAGENS']['RETORNO'] : null;
    }

    /**
     * @return string
     */
    function getMensagemRetorno()    {
        return $this->response['MSG_RETORNO'];
    }

    /**
     * @return array
     */
    public function getArray () {
        return $this->response;
    }
}