# Caixa-WebService

# Autor

- [Wallace Silva](https://github.com/jovemnf)

# Instalação com **composer**

```sh
$ composer install
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

dfg