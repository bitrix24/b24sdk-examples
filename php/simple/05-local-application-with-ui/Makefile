# This file is part of the B24PhpSdk package.
#
#  For the full copyright and license information, please view the MIT-LICENSE.txt
#  file that was distributed with this source code.

.DEFAULT_GOAL := help
MAKEFLAGS += --no-print-directory

.EXPORT_ALL_VARIABLES:
ROOT_DIR := $(shell pwd)

help:
	@echo "Bitrix24 PHP SDK examples"
	@echo "Local application with UI"
	@echo "-------------------------"
	@echo ""
	@echo "docker-init               - first installation"
	@echo "docker-up                 - run docker"
	@echo "docker-down               - stop docker"
	@echo "docker-down-clear         - stop docker and remove orphaned containers"
	@echo "docker-pull               - download containers and ignore pull failures"
	@echo "npm-install               - install dependencies from npm"
	@echo "npm-build                 - build frontend"
	@echo "composer-install          - install dependencies from composer"
	@echo "composer-update           - update dependencies from composer"
	@echo "composer-dumpautoload     - regenerate composer autoload file"
	@echo "composer                  - run composer and pass arguments"
	@echo "php-dev-server-up         - start php dev-server"
	@echo "php-dev-server-down       - stop php dev-server"
	@echo "ngrok-up                  - start ngrok"
	@echo "ngrok-down                - stop ngrok"
	@echo "php-cli-bash              - run container php-cli and open shell with arguments"
	@echo "app-cli                   - run default application command in php-cli container"
	@echo ""

# init project
.PHONY: docker-init
docker-init:
	@echo "remove all containers"
	docker-compose down --remove-orphans
	@echo "build containers"
	docker-compose build
	@echo "install dependencies"
	docker-compose run --rm php-cli composer install
	@echo "change owner of var folder for access from container"
    docker-compose run --rm php-cli chown -R www-data:www-data /var/www/html/var/
	docker compose run --rm node-cli npm install
	@echo "run application…"
	docker-compose up -d

.PHONY: docker-up
docker-up:
	docker compose up -d

.PHONY: docker-down
docker-down:
	docker compose down --remove-orphans

.PHONY: docker-down-clear
docker-down-clear:
	docker compose down --volumes --remove-orphans

.PHONY: docker-pull
docker-pull:
	docker compose pull --ignore-pull-failures

.PHONY: npm-install
npm-install:
	docker compose run --rm node-cli npm install

.PHONY: npm-build
npm-build:
	docker compose run --rm node-cli npm run build:tailwindcss

#
.PHONY: php-cli-bash
php-cli-bash:
	docker-compose run --rm php-cli sh $(filter-out $@,$(MAKECMDGOALS))

.PHONY: app-cli
app-cli:
	docker-compose run --rm php-cli php bin/console b24:test-command

.PHONY: composer-install
composer-install:
	@echo "install dependencies…"
	docker-compose run --rm php-cli composer install

.PHONY: composer-update
composer-update:
	@echo "update dependencies…"
	docker-compose run --rm php-cli composer update

.PHONY: composer-dumpautoload
composer-dumpautoload:
	docker-compose run --rm php-cli composer dumpautoload

# call composer with any parameters
# make composer install
# make composer "install --no-dev"
.PHONY: composer
composer:
	docker-compose run --rm php-cli composer $(filter-out $@,$(MAKECMDGOALS))

# work dev environment
.PHONY: php-dev-server-up
php-dev-server-up:
	docker-compose run --rm -p 10080:10080 php-cli php -S 0.0.0.0:10080 -t public

.PHONY: php-dev-server-down
php-dev-server-down:
	docker-compose down --remove-orphans

.PHONY: ngrok-up
ngrok-up:
	ngrok http 127.0.0.1:10080

.PHONY: ngrok-down
ngrok-down:
	@pids=$$(ps aux | grep ' ngrok http' | grep -v 'grep' | awk '{print $$2}'); \
	if [ -n "$$pids" ]; then \
		echo "Killing process(es): $$pids"; \
		echo "$$pids" | xargs kill; \
	else \
		echo "No ngrok process found."; \
	fi






