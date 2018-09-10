<?php

use CaixaWebService\service\Webservice;

date_default_timezone_set('America/Sao_Paulo');

$parametros = array(
    "UNIDADE" => '1234',
    'CODIGO_BENEFICIARIO' => '951955',
    'NOSSO_NUMERO' => '1947658325871322'
);

$ws = new Webservice($parametros);

$param = array (
    "NOSSO_NUMERO" => "12456745674564",
    "DATA_VENCIMENTO" => "12121212"
);

try {
    echo phpinfo();
    //print_r($ws->Consulta($param));
} catch (Exception $e) {
    echo $e;
}
