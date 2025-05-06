# Call an incoming webhook with cURL 

**❗do not use this template in production**

Call REST-API for education purposes.

## Folder structure
```
  docker               - docker contatainers
    php-cli            - default php-cli container
  src                  - source code
  .env                 - environment variables
  .env.local           - uncommitted file with local overrides
  .gitignore           - gitignore file
  docker-compose.yaml  - docker compose file
  Makefile             - makefile
  README.md            - readme file with instructions    
```
## Architecture
1. All code run in a docker container.
2. Typical operations described in Makefile

To work with Bitrix24 REST API you must create an incoming webhook.

After that add webhook url in new file `.env.local`
```
BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL=https://your-bitrix24-portal-url
```
## How to work with example
1. See all available commands in a make file 
```shell
make
```
2. Build docker containers
```shell
make docker-build
```

3. Enter into docker container `php-cli` and run example
```shell
make php-cli-bash
php -f src/example.php
```

