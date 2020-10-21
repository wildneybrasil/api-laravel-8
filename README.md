# Teste Back End 2020 #

O teste consiste em criar uma mini API Rest utilizando Laravel como framework PHP, JWT como autenticação e um simples CRUD.
Aqui na DIN DIGITAL nós costumamos trabalhar o desenvolvimento utilizando Docker como ambiente local ([Laradock](https://laradock.io/)), então este repositório já está preparado para configurar a instância, basta possuir os [requisitos de software](#software-requirements) e executar o [comando de configuração automática do ambiente](#automatic-setup)

<a name="software-requirements"></a>
## Requisitos de software para o ambiente ##
- Algum terminal de comandos
- [Docker](https://www.docker.com/get-started)
- [Docker-compose](https://docs.docker.com/compose/install/)

<a name="automatic-setup"></a>
## Comando para configuração automática do ambiente ##
Copie o bloco de commandos abaixo e cole num terminal dentro de sua pasta de projetos
OBS: Este commando inclui o próprio git clone
No final você terá um ambiente LAMP e um Laravel 5.8, pronto para ser trabalhado, dentro da pasta site.

```shell
echo -e "\033[1;92m Clonando repositório...\033[m" &&\
git clone git@github.com:dindigital/teste-back-end-2020.git &&\
echo -e "\033[1;92m Entrando na pasta do repositório clonado...\033[m" &&\
cd teste-back-end-2020 &&\
echo -e "\033[1;92m Entrando na pasta docker...\033[m" &&\
cd docker &&\
echo -e "\033[1;92m Buildando o docker...\033[m" &&\
docker-compose build &&\
echo -e "\033[1;92m Ligando o docker...\033[m" &&\
docker-compose up -d &&\
echo -e "\033[1;92m Criando arquivo .env...\033[m" &&\
ln -s .env.example ../laravel/.env &&\
echo -e "\033[1;92m Composer install...\033[m" &&\
docker-compose exec --user=laradock workspace sh -c "cd /var/www/laravel && composer -vvv install --no-scripts" &&\
while ! docker-compose exec mysql mysqladmin --user=root --password=root --host "127.0.0.1" ping --silent &> /dev/null ; do
    echo "Waiting for database connection..."
    sleep 2
done
echo -e "\033[1;92m Artisan migrate...\033[m" &&\
docker-compose exec --user=laradock workspace sh -c "cd /var/www/laravel && php artisan migrate:fresh --seed" &&\
echo -e "\033[1;92m Acesso em: hhttp://localhost\033[m"
```

## Por onde começar ##
Após ligar o docker será possível conectar-se a um container chamado Workspace.
Dentro dele é possível executar comandos como php artisan, composer, etc.
Para acessar a workspace digite o comando abaixo dentro da pasta docker
```shell
docker-compose exec --user=laradock workspace bash
```
Exemplo:
```shell
$ docker-compose exec --user=laradock workspace bash                     
laradock@b1a67c89c959:/var/www$ cd laravel/
laradock@b1a67c89c959:/var/www/laravel$ php artisan make:migration CreateFooTable
Created Migration: 2020_10_21_152543_create_foo_table
```

## Requisitos de desenvolvimento ##
- [Postman](https://www.getpostman.com/downloads/) - Utilizar esta collection / documentaçao: 
https://documenter.getpostman.com/view/10158358/TVYC8z5X
- Utilizar biblioteca: [laravel-responder](https://github.com/flugger/laravel-responder)
- Utilizar biblioteca: [jwt-auth](https://jwt-auth.readthedocs.io/en/develop/laravel-installation/) 

## O que será avalidado ##
- Funcionar o ambiente docker
- Ao abrir a collection do Postman, temos 4 rotas (referentes a Autenticação) com exemplos de como devem funcionar (inputs/outputs). Desenvolver essas rotas de forma que ao executá-las no Postman, os resultados sejam exibidos conforme o descrito na doc.
- Criar 4 rotas **privadas** referentes à um CRUD de produtos. As informações poderão ser: "Nome", "Preço", "Peso".
- Atualizar a documentação do Postman
- Enviar por e-mail o link de seu fork (não precisa criar PR) junto com o link de sua documentação do Postman
- Obrigado e boa sorte =)
