# Caixa-WebService

# Autor

- [Wallace Silva](https://github.com/jovemnf)

# Instalação com **composer**

```console
docker-compose up

docker exec -it cef bash

php composer.phar update --no-interaction --ansi
```

## Usando


### URL Amigavel

Crie um simples arquivo .htaccess no seu diretório principal, se você estriver 
usando Apache com mod_rewrite habilitado.

```apache
Options +FollowSymLinks
RewriteEngine On
RewriteRule ^(.*)$ index.php [NC,L]
```

Se você estiver usando nginx, coloque a sessão do server assim:

```nginx
server {
	listen 80;
	server_name site.dev;
	root /srv/www/site/public;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		# NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

		# With php5-fpm:
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi.conf;
		fastcgi_intercept_errors on;
	}
}
```

## Exemplos

### Configuração

Copie o arquivo '.env.sample' para '.env' e altere a chave secreta no arquivo.

```dotenv
# .env
KEY_SECRET=secret
```

Esta chave será usada para autenticação na API.

### Autenticação

Para autenticar você deve gerar um JWT (Json Web Token) com o valor que esta na 
chave KEY_SECRET e mandar no header da solicitação.

```php

    $JWT = 'eyJhbGciOiJIUzI1NiJ9.MTIzNA.iCjw1e0X2IiE0R_WsktYTJKi6TiJp1_nzbEicYlfUV8';
    
    "Authorization", "Bearer " . $JWT
    
```

### Consultando boleto

JSON enviado a rota /consultar

```json
{
	"HEADER": {
        "CODIGO_BENEFICIARIO": 123456, /* código do convênio */
        "NOSSO_NUMERO": "14000001000267448", // numero do boleto com 17 dígitos a ser consultado
        "UNIDADE": 1234, // agencia bancaria de onde o boleto foi gerado
        "CNPJ": "10884488000122" // CNPJ da empresa emissora
    }
}
```


Valores retornados 

```json
{
    "status": 200,
    "text": "EM ABERTO",
    "paid": false, // true para boleto já pago e false para boleto aberto
    "returned": {
        "HEADER": {
            "VERSAO": "2.0",
            "AUTENTICACAO": "dfghdfghsdfhg", // token de autenticação com a caixa
            "USUARIO_SERVICO": "SGCBS02P",
            "OPERACAO": "CONSULTA_BOLETO",
            "SISTEMA_ORIGEM": "SIGCB",
            "UNIDADE": "1234",
            "IDENTIFICADOR_ORIGEM": "::1",
            "DATA_HORA": "20180906162017",
            "ID_PROCESSO": "278972"
        },
        "COD_RETORNO": "00",
        "ORIGEM_RETORNO": "CONSULTA_COBRANCA_BANCARIA",
        "MSG_RETORNO": "",
        "DADOS": {
            "CONTROLE_NEGOCIAL": {
                "ORIGEM_RETORNO": "SIGCB",
                "COD_RETORNO": "0",
                "MENSAGENS": {
                    "RETORNO": "(0) OPERACAO EFETUADA - SITUACAO DO TITULO = EM ABERTO"
                }
            },
            "CONSULTA_BOLETO": {
                "TITULO": {
                    "NUMERO_DOCUMENTO": "1000267448",
                    "DATA_VENCIMENTO": "2018-08-21",
                    "VALOR": "59.90",
                    "TIPO_ESPECIE": "4",
                    "FLAG_ACEITE": "N",
                    "DATA_EMISSAO": "2018-07-03",
                    "JUROS_MORA": {
                        "TIPO": "VALOR_POR_DIA",
                        "DATA": "2018-08-22",
                        "VALOR": "0.02"
                    },
                    "VALOR_ABATIMENTO": "0.00",
                    "POS_VENCIMENTO": {
                        "ACAO": "DEVOLVER",
                        "NUMERO_DIAS": "120"
                    },
                    "CODIGO_MOEDA": "9",
                    "PAGADOR": {
                        "CPF": "16192088717",
                        "NOME": "TESTE BARRETO ALVES",
                        "ENDERECO": {
                            "LOGRADOURO": "RUA 03",
                            "BAIRRO": "PARQUE NOVO JOC",
                            "CIDADE": "CAMPOS DOS GOYT",
                            "UF": "RJ",
                            "CEP": "28100000"
                        }
                    },
                    "MULTA": {
                        "DATA": "2018-08-22",
                        "PERCENTUAL": "2.00"
                    },
                    "VALOR_IOF": "0.00",
                    "IDENTIFICACAO_EMPRESA": "1000267448",
                    "PAGAMENTO": {
                        "QUANTIDADE_PERMITIDA": "1",
                        "TIPO": "NAO_ACEITA_VALOR_DIVERGENTE",
                        "VALOR_MAXIMO": "0.00",
                        "VALOR_MINIMO": "0.00"
                    },
                    "CODIGO_BARRAS": "10493763900000061412789728034534534534574484",
                    "LINHA_DIGITAVEL": "10492789792803453453453453453453453453576390000006141",
                    "URL": "https://boletoonline.caixa.gov.br/ecobranca/SIGCB/imprimir/123456/14000001456467448"
                },
                "FLAG_REGISTRO": "S"
            }
        }
    }
}
```
### Incluindo boleto

JSON enviado a rota /incluir


```json
{
	"HEADER": {
        "CODIGO_BENEFICIARIO": 123456,
        "NOSSO_NUMERO": "14000050000000001",
        "UNIDADE": 1234,
        "CNPJ": "10584466006703"
    },
    "DADOS": {
    	"NOSSO_NUMERO": "14000050000000001",
	    "NUMERO_DOCUMENTO": "50000000001",
	    "DATA_VENCIMENTO": "2019-12-01",
	    "VALOR": 2000.00,
	    "FLAG_ACEITE": "N",
	    "DATA_EMISSAO": "2018-09-01",
	    "JUROS_MORA": {
	        "TIPO": "ISENTO",
	        "VALOR": 0
	    },
	    "PAGAMENTO": {
	        // não sei o motivo mas, se QUANTIDADE_PERMITIDA vier após TIPO a CEF rejeita o boleto
	    	"QUANTIDADE_PERMITIDA": 20,
	        "TIPO": "ACEITA_QUALQUER_VALOR",
	        "VALOR_MINIMO": 10,
	        "VALOR_MAXIMO": 2000
	    },
	    "POS_VENCIMENTO":{
	        "ACAO": "DEVOLVER",
	        "NUMERO_DIAS": 0
	    },
	    "PAGADOR": {
	        "CPF": "09772938567",
	        "NOME": "TESTE BARRETO ALVES",
	        "ENDERECO": {
	            "LOGRADOURO": "AV PELINCA, 245",
	            "BAIRRO": "PELINCA",
	            "CIDADE": "CAMPOS",
	            "UF": "RJ",
	            "CEP": "28035053"
	        }
	    }
    }
}
```

Caso tenha inserido retornará o seguinte

```json
{
    "status": 200,
    "text": "",
    "returned": {
        "HEADER": {
            "VERSAO": "2.0",
            "AUTENTICACAO": "LQf93vlh778889rJ/4l0tTExw0Ucwh5999y4oqw/Q4yGE=",
            "USUARIO_SERVICO": "SGCBS02P",
            "OPERACAO": "INCLUI_BOLETO",
            "SISTEMA_ORIGEM": "SIGCB",
            "UNIDADE": "1234",
            "IDENTIFICADOR_ORIGEM": "::1",
            "DATA_HORA": "20180918090050",
            "ID_PROCESSO": "278972"
        },
        "COD_RETORNO": "00",
        "ORIGEM_RETORNO": "MANUTENCAO_COBRANCA_BANCARIA",
        "MSG_RETORNO": "",
        "DADOS": {
            "CONTROLE_NEGOCIAL": {
                "ORIGEM_RETORNO": "SIGCB",
                "COD_RETORNO": "0",
                "MENSAGENS": {
                    "RETORNO": "(0) OPERACAO EFETUADA"
                }
            },
            "INCLUI_BOLETO": {
                "CODIGO_BARRAS": "10491809000002000002789728000105040000000010",
                "LINHA_DIGITAVEL": "10492789792800010504000000000109180900000200000",
                "NOSSO_NUMERO": "0",
                "URL": "https://boletoonline.caixa.gov.br/ecobranca/SIGCB/imprimir/0278972/14000050000000001"
            }
        }
    }
}
```