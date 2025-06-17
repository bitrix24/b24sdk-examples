# Add monolog for logging operations with b24-php-sdk 

**❗do not use this template in production**

Call REST-API for education purposes.

## Folder structure
```
  docker               - docker contatainers
    php-cli            - default php-cli container
  src                  - source code
    example.php        - example application
  var                  - source code
    logs               - directory with application logs on host machine
  vendor               - folder with dependencies from composer.json  
  .env                 - environment variables
  .env.local           - uncommitted file with local overrides  
  .gitignore           - gitignore file
  composer.json        - composer file
  composer.lock        - composer lock file
  docker-compose.yaml  - docker compose file
  Makefile             - makefile
  README.md            - readme file with instructions    
```
## Architecture
1. All code run in a docker container.
2. Typical operations described in Makefile

To work with Bitrix24 REST API you **must** create an incoming webhook in your dev-portal with scope `crm`.

## How to work with example

1. Add webhook url in new file `.env.local`
```
BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL=https://your-bitrix24-portal-url
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

5. Enter into docker container `php-cli` and run example
```shell
make php-cli-bash
php -f src/example.php
```

6. You also can run an example with a call docker compose of your host system
```shell
make php-cli-app
```

## Must read documentation
- [Docker fundamentals](https://docs.docker.com/get-started/introduction/)
- [Makefile tutorial](https://makefiletutorial.com/#getting-started)
- [Composer](https://getcomposer.org/doc/)
- [PSR-3](https://www.php-fig.org/psr/psr-3/)
- [RFC-5424 The Syslog Protocol](https://datatracker.ietf.org/doc/html/rfc5424)
- [Monolog](https://github.com/Seldaek/monolog)
- [tail](https://man7.org/linux/man-pages/man1/tail.1.html)
- [grep](https://man7.org/linux/man-pages/man1/grep.1.html)
- [lnav](https://lnav.org/)