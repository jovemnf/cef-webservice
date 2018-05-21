<?php

include('./service/Webservice.php');

$parametros = array(
    'FORMATO_RETORNO' => 'ARRAY',
    'CODIGO_BENEFICIARIO' => '951955',
    'NOSSO_NUMERO' => '1947658325871322',
    'NUMERO_DOCUMENTO' => '674389152',
    'DATA_VENCIMENTO' => '2017-10-18',
    'VALOR' => '81.53',
    'FLAG_ACEITE' => 'N',
    'DATA_EMISSAO' => '2017-10-18',
    'NUMERO_DIAS' =>  '30',
    'PAGADOR' => array(
        'CPF' => '0036893461927',
        'NOME' => 'CARLOS FERNANDO ROSA',
        'ENDERECO' => array(
            'LOGRADOURO' => 'ROD ADMAR GONZAGA, 1823',
            'BAIRRO' => 'ITACORUBI',
            'CIDADE' => 'FLORIANOPOLIS',
            'UF' => 'SC',
            'CEP' => '88034000',
        ),
        'FICHA_COMPENSACAO' => array(
            'MENSAGENS' => array(
                'MENSAGEM1' => 'PRIMEIRA LINHA DA MENSAGEM',
                'MENSAGEM2' => 'SEGUNDA LINHA DA MENSAGEM'
            )
        )
    )
);

try {
    $ws = new WebserviceCaixa($parametros);
    $ws->Gera();
} catch (Exception $e) {
    echo $e;
}
