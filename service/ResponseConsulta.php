<?php

namespace CaixaWebService\service;

class ResponseConsulta extends ResponseBase
{

    function __construct(array $arr){
        $this->response = $arr;
    }

    function getPagadorNome()        {
        return (isset($this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['NOME'])) ?
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['NOME'] :
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['RAZAO_SOCIAL'];
    }

    function getPagadorNumero()      {
        return (isset($this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['CPF'])) ?
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['CPF'] :
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['CNPJ'];
    }

    function getPagadorLogradouro()  { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['ENDERECO']['LOGRADOURO']; }
    function getPagadorCidade()      { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['ENDERECO']['CIDADE']; }
    function getPagadorBairro()      { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['ENDERECO']['BAIRRO']; }
    function getPagadorUf()          { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['ENDERECO']['UF']; }
    function getPagadorCep()         { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['PAGADOR']['ENDERECO']['CEP']; }
    function getDataEmissao()        { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['DATA_EMISSAO']; }
    function getDataVencimento()     { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['DATA_VENCIMENTO']; }
    function getValor()              { return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['VALOR']; }

    function getMensagem1()          {
        return (is_array($this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['FICHA_COMPENSACAO']['MENSAGENS']['MENSAGEM'])) ?
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['FICHA_COMPENSACAO']['MENSAGENS']['MENSAGEM'][0] :
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['FICHA_COMPENSACAO']['MENSAGENS']['MENSAGEM'] ;
    }

    function getMensagem2()          {
        return (is_array($this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['FICHA_COMPENSACAO']['MENSAGENS']['MENSAGEM'])) ?
        $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['FICHA_COMPENSACAO']['MENSAGENS']['MENSAGEM'][1] : '' ;
    }

    function getNumeroDocumento()    {
        return $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['NUMERO_DOCUMENTO'];
    }

    function getUrl()    {
        return (isset($this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['URL']))
            ? $this->response['DADOS']['CONSULTA_BOLETO']['TITULO']['URL'] : null;
    }

}