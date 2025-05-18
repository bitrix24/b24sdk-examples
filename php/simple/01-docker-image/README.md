# Empty application template 

**❗do not use this template in production**

Application template for education purposes.

## Folder structure
```
  docker               - docker contatainers
    php-cli            - default php-cli container
  src                  - source code
  docker-compose.yaml  - docker compose file
  Makefile             - makefile
  README.md            - readme file with instructions    
```
## Architecture
1. All code run in a docker container.
2. Typical operations described in Makefile

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
4. You also can run an example with a call docker compose of your host system 
```shell
make php-cli-app-run
```