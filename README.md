# Promobit Auth Service

Este projeto é um serviço de autenticação para teste da empresa Promobit feito com o framework Symfony versão 5.1, banco de dados MariaDB e MongoDB e servidor web NGINX.

## 💻 Pré-requisitos

Antes de começar, é necessário ter os seguintes pré-requisitos instalados no seu computador:

* Docker ([Tutorial de instalação](https://www.docker.com/get-started))

* Postman (Para testes dos endpoints da api) --&gt; [Download](https://www.postman.com/downloads/)

## 🚀 Instalando o projeto

Para instalar e rodar o projeto na sua máquina local, basta executar os seguintes passos:

* Faça uma cópia do arquivo 'env.example' e renomeie para '.env';

* No arquivo '.env' insira as credenciais que irão ser usadas para criar o banco de dados;

----------

Após preparar o arquivo .env, execute o seguinte comando:

```bash
docker-compose up -d
```

Se tudo der certo, o projeto já estará rodando e para acessá-lo basta colocar o ip do container (172.60.0.5) ou a url (0.0.0.0:14000) no seu navegador.

## Configurando o projeto e instalando dependências

Entre na pasta do projeto Symfony 'auth-service' e faça os seguintes passos:

* Faça uma cópia do arquivo 'env.example' e renomeie para '.env';

* No arquivo .env há algumas variáveis que precisam ser setadas obrigatoriamente para que o projeto funcione sem problemas.

  * Variável APP_SECRET: Gerar uma string alfanumérica aleatória com 32 caracteres. Pode ser gerada por esse site -> `http://www.unit-conversion.info/texttools/random-string-generator/`
  
  * Variável DATABASE_URL: Descomentar a linha correspondente a conexão com o banco MariaDB (indicado na linha) e colocar as credencias de acesso ao banco (as mesmas usadas no outro arquivo .env para criação do banco). Um exemplo de como ficará a string de conexão com o banco:
  
    * DATABASE_URL="mysql://`usuario_do_banco`:`senha_do_banco`@`url_do_banco`:3306/`nome_do_database`?serverVersion=mariadb-10.6.4

  * Variável MAILER_DSN: Esta variável é usada para a conexão SMTP para envio de emails. Aqui poderá ser usada a string do serviço de sua preferência. Um dos serviços que pode ser utilizado é o [Mailtrap](https://mailtrap.io/) bastando apenas fazer o cadastro gratuíto no site

  * Variável MONGODB_URL: Usada para conectar com o banco MongoDB. Usar também as mesmas credenciais cadastradas no outro arquivo .env na criação do banco. Exemplo:

    * MONGODB_URL=mongodb://`usuario_do_banco`:`senha_do_banco`@`url_do_banco`:27017

* Após setar as variáveis no arquivo .env é preciso instalar as dependências do projeto, que é feita através do comando:

  ```bash
  docker exec -it promobit-api sh -c "composer install"
  ```

  * Serão criadas as pastas 'vendor' e 'var' na raiz do projeto. Para não ter nenhum problema de acesso na pasta 'var' execute o seguinte comando:

  ```bash
  docker exec -it promobit-api sh -c "chmod -R 777 var"
  ```

* É preciso criar as tabelas no banco para armazenar as informações da aplicação. Para isso execute o seguinte comando:

```bash
docker exec -it promobit-api sh -c "bin/console doctrine:migrations:migrate"
```

* Após a criação do banco é preciso gerar as chaves utilizadas para gerar os tokens de autenticação da api. Para isso execute o seguinte comando:

```bash
docker exec -it promobit-api sh -c "bin/console lexik:jwt:generate-keypair"
```

## Bancos de dados

Esta aplicação usa 2 bancos de dados (MariaDB e MongoDB), que podem ser acessado através de qualquer programa gerenciador (MySQL Workbench, DBeaver no caso do MariaDB ou Robo3T, Compass no caso do MongoDB)

## Endpoints Postman

Na raiz deste projeto há um arquivo json de [Coleção do Postman](promobit_collection.json) onde estão todos os endpoints. Para importar o arquivo no Postman, basta ir em "File > Import" e buscar o arquivo na pasta.

## Executando os endpoints

As rotas internas da api (cadastro, pesquisa, update, etc) estão protegidas e só são acessadas caso o usuário esteja "logado". Neste caso, como a api é stateless é preciso gerar e enviar o token no Header em cada requisição que for realizada.

* Para gerar o token, basta ir no endpoint de login (auth/login) e colocar o usuário e senha. A resposta será o token que precisará ser colocado no header de cada endpoint protegido.

## RabbitMQ e Supervisor

Para o envio do reset de senha foi adicionado o serviço do RabbitMQ. Para acessar o painel administrativo, basta acessar no navegador o endereço: `localhost:8787` e entrar com o usuário 'promobit' e a senha 'passwd'. Ao enviar o reset de senha o Rabbit já executará a fila, pois foi adicionado também o supervisor para rodar um consumer da fila em background.

## License

Este projeto é licenciado pela [Licença MIT](https://opensource.org/licenses/MIT)
