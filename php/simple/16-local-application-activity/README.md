# Local Bitrix24 application with UI and workflow activity

**❗do not use this template in production**

Call REST-API for education purposes.

## Folder structure

```
  bin
    console                           - symfony console CLI entrypoint
  config                              
    auth.json.local                   - json file with applicaiton auth credentials           
  docker                              - docker contatainers
    php-cli                           - default php-cli container
  public                              - public folder served by PHP dev-server  
    index.php                         - default entrypoint for http requests
    install.php                       - bitrix24 events handler entrypoint
    robots.txt                        - static file example
  src                                 - source code, PSR-4 autoload
    Controller                        - folder with http-controllers
        InstallController.php         - installation flow processing controller
    Events                            
        Bitrix24EventListener.php     - Bitrix24 PHP SDK event listener, listen AuthTokenRenewedEvent event     
        EventDispatcherFactory.php    - Event Dispatcher factory
    Infrastructure                    
        Console                       
            TestCommand.php           - Test CLI command, based on Symfony Console component
    Repository                        
        AuthRepositoryFactory.php     - Auth repository Factory, use AppAuthFileStorage from SDK
    Bitrix24ServiceBuilderFactory.php - Bitrix24 service builder factory, init SB from stroed auth token or incoming event
    LoggerFactory.php                 - Monolog logger factory
  tests                               - tests folder
    bootstrap.php                     - bootsrtap file for tests                                 
  var                                 - temp files
    logs                              - directory with application logs on host machine
  vendor                              - folder with dependencies from composer.json
  .allowed-licenses.php               - allowed licenses for composer dependencies
  .env                                - environment variables
  .env.local                          - uncommitted file with local overrides  
  .gitignore                          - gitignore file
  .php-cs-fixer.php                   - PHP CS Fixer rules 
  composer.json                       - composer file
  composer.lock                       - composer lock file
  docker-compose.yaml                 - docker compose file
  Makefile                            - makefile
  phpstan.neon.dist                   - phpstan settings file
  README.md                           - readme file with instructions
  rector.php                          - rector settings file    
```

## Architecture

1. All code run in a docker container.
2. Typical operations described in Makefile

To work with Bitrix24 REST API you **must** create a local application without UI in your dev-portal with scope `crm` and `user_brief`.

3. All files in `src` folder must be checked with linters:

- PHP CS Fixer
- phpstan
- rector
- allowed licenses

See makefile, section «Work with linters and checks»

## How to work with example

1. Add parameters for local Bitrix24 application to  `.env.local` file

```
BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID=INSERT_YOUR_CLIENT_ID_HERE
BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET=INSERT_YOUR_APPLICATION_CLIENT_SECRET_HERE
BITRIX24_PHP_SDK_APPLICATION_SCOPE=crm,user_brief
```

2. See all available commands in a make file

```shell
make
```

3. Build docker containers

```shell
make docker-build
```

4. Install dependencies from composer

```shell
make composer-install
```

5. Start PHP-local DEV-server

```shell
make php-dev-server-up
```

6. Start ngrok for pass traffic from the internet

```shell
make php-dev-server-up
```

7. Install local application in your Bitrix24 portal

8. Check application logs in folder `/var/logs/`

9. Run demo-application from CLI
```shell
make php-cli-app
```


=============================


1 - делаем аккуратно
1-5 - роботов уже надо делать структуру единообразную 
5+ - без единоообразной структуры будет больно

============================

1. Роботы обслуживают одну предметную область внутри Б24
CRM, Смарт-процессы, связь и т.д.

- все роботы +\- единообразны
- все роботы могут быть сделаны внутри монорепозитория

- обработчики можно запускать в количестве N экземпляров
- роутинг на какой обработчик пойдет выполнение происходит на стороне PHP

=> входящий запрос от Б24 => контроллер роботов => очередь => воркер => маршрутизация на нужного робота по robot_code => нужный робот

N роботов = 1 очередь = X однотипных воркеров

-----------------------------
Роботы обслуживают разные сценарии \ используется разный технологический стек

=> входящий запрос от Б24 => контроллер роботов (выставляем routing_key)  => очередь => роутинг в нужную очередь => обработчик конкретной очереди

N роботов = N очередей = N(N*2) разных воркеров в разных докер-контейнерах

- робот 1 = PHP 
- робот 2 = Python
- робот 3 = NodeJS




























