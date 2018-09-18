<?php

namespace CaixaWebService\service;

use CaixaWebService\lib\Words;
use CaixaWebService\lib\XmlDomConstruct;

class Webservice {

	var $args;
	var $consulta;

	use Words;
	use RequestSoap;

    const wsdl_consulta = 'https://barramento.caixa.gov.br/sibar/ConsultaCobrancaBancaria/Boleto?WSDL';
    const wsdl_manutencao = 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo?WSDL';

	/**
	 * Construtor atribui e formata parâmetros em $this->args
     * @param array $args
	 */
	public function __construct($args) {
		$this->args = $this->CleanArray($args);
        $this->consulta['IDENTIFICADOR_ORIGEM'] = $_SERVER['REMOTE_ADDR'];
		$this->consulta['CEDENTE'] = isset($this->args['CEDENTE']) ? $this->args['CEDENTE'] : null;
		$this->consulta['IDENTIFICACAO'] = isset($this->args['IDENTIFICACAO']) ? $this->args['IDENTIFICACAO'] : null;
		$this->consulta['ENDERECO1'] = isset($this->args['ENDERECO1']) ? $this->args['ENDERECO1'] : null;
		$this->consulta['ENDERECO2'] = isset($this->args['ENDERECO2']) ? $this->args['ENDERECO2'] : null;
		$this->consulta['CNPJ'] = $this->args['CNPJ'];
		$this->consulta['UNIDADE'] = isset($this->args['UNIDADE']) ? $this->args['UNIDADE'] : null;
		$this->consulta['CODIGO_BENEFICIARIO'] = $this->args['CODIGO_BENEFICIARIO'];
		$this->consulta['NOSSO_NUMERO'] = $this->args['NOSSO_NUMERO'];
	}

    /**
     * Cálculo do Hash de autenticação segundo página 7 do manual.
     * @return string
     * @throws \Exception
     */
	private function HashAutenticacao() {
	    try {
            $numeroParaHash = preg_replace('/[^A-Za-z0-9]/', '',
                    str_pad($this->GetCodigoBeneficiario() , 7, '0', STR_PAD_LEFT) .
                    $this->GetNossoNumero() .
                    strftime('%d%m%Y', strtotime($this->getDataVencimento()))) .
                str_pad(str_replace('.', '', number_format($this->getValor(), 2, '.', '')), 15, '0', STR_PAD_LEFT) .
                str_pad($this->GetCnpj(), 14, '0', STR_PAD_LEFT);

            return base64_encode(hash('sha256', $numeroParaHash, true));
        } catch (\Exception $e) {
	        throw $e;
        }
	}

    /**
     * Construção do documento XML para consultas.
     * @return string
     * @throws \Exception
     */
	private function createXmlConsulta() {
	    try {

            // Para consultas, DATA_VENCIMENTO e VALOR devem ser preenchidos com zeros
            $autenticacao = $this->HashAutenticacao();

            $xml_array = array(
                'sibar_base:HEADER' => array(
                    'VERSAO' => '2.0',
                    'AUTENTICACAO' => $autenticacao,
                    'USUARIO_SERVICO' => 'SGCBS02P',
                    'OPERACAO' => 'CONSULTA_BOLETO',
                    'SISTEMA_ORIGEM' => 'SIGCB',
                    'UNIDADE' => $this->GetUnidade(),
                    'IDENTIFICADOR_ORIGEM' => $this->consulta['IDENTIFICADOR_ORIGEM'],
                    'DATA_HORA' => date('YmdHis'),
                    'ID_PROCESSO' => $this->GetCodigoBeneficiario()
                ),
                'DADOS' => array(
                    'CONSULTA_BOLETO' => array(
                        //'CODIGO_BENEFICIARIO' => $args['CODIGO_BENEFICIARIO'],
                        'CODIGO_BENEFICIARIO' => $this->GetCodigoBeneficiario(),
                        'NOSSO_NUMERO' => $this->GetNossoNumero(),
                    )
                )
            );

            $xml_root = 'consultacobrancabancaria:SERVICO_ENTRADA';
            $xml = new XmlDomConstruct('1.0', 'iso-8859-1');
            $xml->preserveWhiteSpace = true;
            $xml->formatOutput = false;
            $xml->fromMixed(array($xml_root => $xml_array));
            $xml_root_item = $xml->getElementsByTagName($xml_root)->item(0);

            $xml_root_item->setAttribute('xmlns:consultacobrancabancaria',
                'http://caixa.gov.br/sibar/consulta_cobranca_bancaria/boleto');

            $xml_root_item->setAttribute('xmlns:sibar_base',
                'http://caixa.gov.br/sibar');

            $xml_string = $xml->saveXML();
            $xml_string = preg_replace('/^<\?.*\?>/', '', $xml_string);
            $xml_string = preg_replace('/<(\/)?MENSAGEM[0-9]>/', '<\1MENSAGEM>', $xml_string);

            return $xml_string;
        } catch (\Exception $e) {
	        throw $e;
        }
	}

    /**
     * Prepara e executa consultas
     *
     * Parâmetros mínimos para que o boleto possa ser consultado.
     * @return ResponseConsulta
     * @throws \Exception
     */
	public function Consultar() {
	    try {
            $xml = $this->createXmlConsulta();
            $call = $this->Call(Webservice::wsdl_consulta, 'CONSULTA_BOLETO', $xml);
            return new ResponseConsulta($call);
        } catch (\Exception $e) {
	        echo $e;
	        throw $e;
        }
	}

    /**
     * Construção do documento XML para incluir e alterar
     * @param $args
     * @return null|string|string[]
     */
	private function createXmlManutencao($args) {
		$xml_root = 'manutencaocobrancabancaria:SERVICO_ENTRADA';
		$xml = new XmlDomConstruct('1.0', 'iso-8859-1');
		$xml->preserveWhiteSpace = true;
		$xml->formatOutput = false;
		$xml->fromMixed(array($xml_root => $args));
		$xml_root_item = $xml->getElementsByTagName($xml_root)->item(0);

		$xml_root_item->setAttribute('xmlns:manutencaocobrancabancaria',
			'http://caixa.gov.br/sibar/manutencao_cobranca_bancaria/boleto/externo');

		$xml_root_item->setAttribute('xmlns:sibar_base',
			'http://caixa.gov.br/sibar');

		$xml_string = $xml->saveXML();
		$xml_string = preg_replace('/^<\?.*\?>/', '', $xml_string);
		$xml_string = preg_replace('/<(\/)?MENSAGEM[0-9]>/', '<\1MENSAGEM>', $xml_string);

		return $xml_string;
	}

    /**
     * Prepara e executa inclusões e alterações de boleto
     *
     * @param string $operacao INCLUI_BOLETO ou ALTERA_BOLETO
     * @return array
     * @throws \Exception
     */
	private function Manutencao($xml_array, $operacao) {
        try {
            $xml = $this->createXmlManutencao($xml_array);
            return $this->Call(Webservice::wsdl_manutencao, $operacao, $xml);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Realiza a operação de inclusão
     *
     * Parâmetros mínimos para que o boleto possa ser incluído.
     * @param array $args
     * @return ResponseInsert
     * @throws \Exception
     */
	public function Inserir($args) {
        try {
            $xml_array = $this->createXmlInsert($args);
		    $xml = $this->Manutencao($xml_array, 'INCLUI_BOLETO');
            return new ResponseInsert($xml);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Realiza a operação de inclusão
     *
     * Parâmetros mínimos para que o boleto possa ser incluído.
     * @return ResponseBaixa
     * @throws \Exception
     */
    public function Baixar() {
        try {
            $xml_array = $this->createXmlBaixa();
            return new ResponseBaixa($this->Manutencao($xml_array, 'BAIXA_BOLETO'));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Realiza a operação de alteração
     *
     * Parâmetros mínimos para que o boleto possa ser alterado.
     * @param array $args
     * @return ResponseUpdate
     * @throws \Exception
     */
	public function Atualizar($args) {
        try {
            $xml_array = $this->createXmlUpdate($args);
            return new ResponseUpdate($this->Manutencao($xml_array, 'ALTERA_BOLETO'));
        } catch (\Exception $e) {
            throw $e;
        }
    }
	
	/*** Getters ***/
    private function GetCedente()            { return $this->consulta['CEDENTE']; }
    private function GetIdentificacao()      { return $this->consulta['IDENTIFICACAO']; }
    private function GetCnpj()               { return $this->consulta['CNPJ']; }
    private function GetEndereco1()          { return $this->consulta['ENDERECO1']; }
    private function GetEndereco2()          { return $this->consulta['ENDERECO2']; }
    private function GetUnidade()            { return $this->consulta['UNIDADE']; }
    private function GetCodigoBeneficiario() { return $this->consulta['CODIGO_BENEFICIARIO']; }
    private function GetNossoNumero()        { return $this->consulta['NOSSO_NUMERO']; }
    private function GetFlagAceite()         { return $this->args['FLAG_ACEITE']; }

    private function getValor()             {
	    return (isset($this->args['VALOR'])) ? $this->args['VALOR'] : 0;
	}

    private function getDataVencimento()             {
        return (isset($this->args['DATA_VENCIMENTO'])) ? $this->args['DATA_VENCIMENTO'] : 0;
    }

    /**
     * @param $args
     * @return array
     * @throws \Exception
     */
    private function createXmlUpdate($args): array
    {
        $args = array_merge($this->args, $args);
        try {
            $xml_array = array(
                'sibar_base:HEADER' => array(
                    'VERSAO' => '2.0',
                    'AUTENTICACAO' => $this->HashAutenticacao(),
                    'USUARIO_SERVICO' => 'SGCBS02P',
                    'OPERACAO' => 'ALTERA_BOLETO',
                    'SISTEMA_ORIGEM' => 'SIGCB',
                    'UNIDADE' => $this->GetUnidade(),
                    'IDENTIFICADOR_ORIGEM' => $this->consulta['IDENTIFICADOR_ORIGEM'],
                    'DATA_HORA' => date('YmdHis'),
                    'ID_PROCESSO' => $this->GetCodigoBeneficiario(),
                ),
                'DADOS' => array(
                    'ALTERA_BOLETO' => array(
                        'CODIGO_BENEFICIARIO' => $this->GetCodigoBeneficiario(),
                        'TITULO' => array(
                            'NOSSO_NUMERO' => $args['NOSSO_NUMERO'],
                            'NUMERO_DOCUMENTO' => $args['NUMERO_DOCUMENTO'],
                            'DATA_VENCIMENTO' => $args['DATA_VENCIMENTO'],
                            'VALOR' => $args['VALOR'],
                            'TIPO_ESPECIE' => '99',
                            'FLAG_ACEITE' => $args['FLAG_ACEITE'],
                            'JUROS_MORA' => array(
                                'TIPO' => 'ISENTO',
                                'VALOR' => '0',
                            ),
                            'VALOR_ABATIMENTO' => '0',
                            'POS_VENCIMENTO' => array(
                                'ACAO' => 'DEVOLVER',
                                'NUMERO_DIAS' => $args['NUMERO_DIAS'],
                            ),
                            'FICHA_COMPENSACAO' => $args['FICHA_COMPENSACAO']
                        ),
                    )
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
        return $xml_array;
    }

    /**
     * @param $args
     * @return array
     * @throws \Exception
     */
    private function createXmlInsert($args): array
    {
        $this->args = $this->CleanArray($args);

        try {
            $xml_array = array(
                'sibar_base:HEADER' => array(
                    'VERSAO' => '2.0',
                    'AUTENTICACAO' => $this->HashAutenticacao(),
                    'USUARIO_SERVICO' => 'SGCBS02P',
                    'OPERACAO' => 'INCLUI_BOLETO',
                    'SISTEMA_ORIGEM' => 'SIGCB',
                    'UNIDADE' => $this->GetUnidade(),
                    'IDENTIFICADOR_ORIGEM' => $this->consulta['IDENTIFICADOR_ORIGEM'],
                    'DATA_HORA' => date('YmdHis'),
                    'ID_PROCESSO' => $this->GetCodigoBeneficiario()
                ),
                'DADOS' => array(
                    'INCLUI_BOLETO' => array(
                        'CODIGO_BENEFICIARIO' => $this->GetCodigoBeneficiario(),
                        'TITULO' => array(
                            'NOSSO_NUMERO' => $this->GetNossoNumero(),
                            'NUMERO_DOCUMENTO' => $args['NUMERO_DOCUMENTO'],
                            'DATA_VENCIMENTO' => $this->getDataVencimento(),
                            'VALOR' => $this->getValor(),
                            'TIPO_ESPECIE' => $args['TIPO_ESPECIE'],
                            'FLAG_ACEITE' => $args['FLAG_ACEITE'],
                            'DATA_EMISSAO' => $args['DATA_EMISSAO'],
                            'JUROS_MORA' => $args['JUROS_MORA'],
                            'VALOR_ABATIMENTO' => $args['VALOR_ABATIMENTO'],
                            'POS_VENCIMENTO' => $args['POS_VENCIMENTO'],
                            'CODIGO_MOEDA' => '09',
                            'PAGADOR' => $args['PAGADOR'],
                            'PAGAMENTO' => $args['PAGAMENTO']
                        )
                    )
                )
            );

            if (isset($args['SACADOR_AVALISTA'])) {
                $xml_array["DADOS"]["INCLUI_BOLETO"]["TITULO"]["SACADOR_AVALISTA"] = $args['SACADOR_AVALISTA'];
            }

            if (isset($args['MULTA'])) {
                $xml_array["DADOS"]["INCLUI_BOLETO"]["TITULO"]["MULTA"] = $args['MULTA'];
            }

            if (isset($args['DESCONTOS'])) {
                $xml_array["DADOS"]["INCLUI_BOLETO"]["TITULO"]["DESCONTOS"] = $args['DESCONTOS'];
            }

            if (isset($args['FICHA_COMPENSACAO'])) {
                $xml_array["DADOS"]["INCLUI_BOLETO"]["TITULO"]["FICHA_COMPENSACAO"] = $args['FICHA_COMPENSACAO'];
            }
            return $xml_array;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function createXmlBaixa(): array
    {
        try {
            $xml_array = array(
                'sibar_base:HEADER' => array(
                    'VERSAO' => '2.0',
                    'AUTENTICACAO' => $this->HashAutenticacao(),
                    'USUARIO_SERVICO' => 'SGCBS02P',
                    'OPERACAO' => 'BAIXA_BOLETO',
                    'SISTEMA_ORIGEM' => 'SIGCB',
                    'UNIDADE' => $this->GetUnidade(),
                    'IDENTIFICADOR_ORIGEM' => $this->consulta['IDENTIFICADOR_ORIGEM'],
                    'DATA_HORA' => date('YmdHis'),
                    'ID_PROCESSO' => $this->GetCodigoBeneficiario(),
                ),
                'DADOS' => array(
                    'BAIXA_BOLETO' => array(
                        'CODIGO_BENEFICIARIO' => $this->GetCodigoBeneficiario(),
                        'NOSSO_NUMERO' => $this->GetNossoNumero()
                    )
                )
            );
            return $xml_array;
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
