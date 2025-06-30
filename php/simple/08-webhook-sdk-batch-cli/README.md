# Add linters phpstan, rector, PHP CS Fixer

**❗do not use this template in production**

Call REST-API for education purposes.

## Folder structure
```
  docker                   - docker contatainers
    php-cli                - default php-cli container
  src                      - source code
  var                      - source code
    logs                   - directory with application logs on host machine
  vendor                   - folder with dependencies from composer.json
  .allowed-licenses.php    - allowed licenses for composer dependencies
  .env                     - environment variables
  .env.local               - uncommitted file with local overrides  
  .gitignore               - gitignore file
  .php-cs-fixer.php        - PHP CS Fixer rules 
  composer.json            - composer file
  composer.lock            - composer lock file
  docker-compose.yaml      - docker compose file
  Makefile                 - makefile
  phpstan.neon.dist        - phpstan settings file
  README.md                - readme file with instructions
  rector.php               - rector settings file    
```
## Architecture
1. All code run in a docker container.
2. Typical operations described in Makefile

To work with Bitrix24 REST API you **must** create an incoming webhook in your dev-portal with scope `crm`.

3. All files in `src` folder must be checked with linters:
- PHP CS Fixer
- phpstan
- rector
- allowed licenses
 
See makefile, section «Work with linters and checks»

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
php -f src/etl-cli-example.php -- --webhook="YOUR_WEBHOOK"
```