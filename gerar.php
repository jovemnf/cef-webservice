<?php

use CaixaWebService\service\Webservice;

$parametros = array(
    'HEADER' => array(
        "CODIGO_BENEFICIARIO" => "278972",
        "UNIDADE" => 2524, // agencia bancaria de onde o boleto foi gerado
        "CNPJ" => "10584488000103" // CNPJ da empresa emissora
    ),
    "NOSSO_NUMERO" => "14000050000000001", // numero do boleto com 17 dígitos a ser consultado
    'NUMERO_DOCUMENTO' => '50000000001',
    'DATA_VENCIMENTO' => '2019-12-01',
    'VALOR' => '2000.00',
    'FLAG_ACEITE' => 'N',
    'DATA_EMISSAO' => '2018-09-01',
    'JUROS_MORA' => array (
        'TIPO' => 'ISENTO',
        'DATA' => 0,
        'VALOR' => 0
    ),
    'PAGAMENTO' => array(
        'TIPO' => 'ACEITA_VALORES_ENTRE_MINIMO_MAXIMO',
        'VALOR_MINIMO' => '10',
        'QUANTIDADE_PERMITIDA' => 20
    ),
    'POS_VENCIMENTO' => array (
        'ACAO' => 'DEVOLVER',
        'NUMERO_DIAS' => 0
    ),
    'PAGADOR' => array(
        'CPF' => '09772938740',
        'NOME' => 'WALLACE PACHECO DA SILVA',
        'ENDERECO' => array(
            'LOGRADOURO' => 'AV PELINCA, 245',
            'BAIRRO' => 'PELINCA',
            'CIDADE' => 'CAMPOS DOS GOYTACAZES',
            'UF' => 'RJ',
            'CEP' => '28035053',
        )
    )
);

try {
    $ws = new Webservice($parametros);
    $ws->Insert($parametros);
} catch (Exception $e) {
    echo $e;
}
