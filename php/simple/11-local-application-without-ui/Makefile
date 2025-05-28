# This file is part of the b24sdk-examples package.
#
# © Maksim Mesilov <mesilov.maxim@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.

# Export current user's UID and GID to be used by Docker for correct file permissions
export UID=$(shell id -u)
export GID=$(shell id -g)

# Load environment variables from .env and optional .env.local (no error if missing)
# All variables will be exported to subprocesses (e.g., Docker, shell commands)
include .env
-include .env.local
export

# Default Docker commands, can be overridden if needed (different setups, old docker-compose, CI quirks, etc.)
DOCKER?=docker
DOCKER_COMPOSE?=docker compose

# Makefile quality-of-life improvements:
# - Default goal = help
# - Cleaner output (no Entering/Leaving directory noise)
.DEFAULT_GOAL := help
MAKEFLAGS += --no-print-directory

# - Export all vars for easier scripting
# - Set project root path
.EXPORT_ALL_VARIABLES:
ROOT_DIR := $(shell pwd)

help:
	@echo "----------------------------------------------"
	@echo "Bitrix24 PHP SDK examples                     "
	@echo "11 — local application without user interface "
	@echo "----------------------------------------------"
	@echo ""
	@echo "🐳 Work with application containers:"
	@echo "docker-build              - build containers"
	@echo "docker-up                 - run docker"
	@echo "docker-down               - stop docker"
	@echo "docker-down-clear         - stop docker and remove orphaned containers"
	@echo ""
	@echo "📦 Work with composer:"
	@echo "composer-install          - install dependencies from composer"
	@echo "composer-update           - update dependencies from composer"
	@echo "composer-dumpautoload     - regenerate composer autoload file"
	@echo "composer                  - run composer and pass arguments"
	@echo ""
	@echo "👮 Work with linters and checks:"
	@echo "lint-allowed-licenses     - check dependencies for white-list licenses"
	@echo "lint-cs-fixer             - check source code with PHP CS Fixer"
	@echo "lint-cs-fixer-fix         - 🪄 fix source code with PHP CS Fixer"
	@echo "lint-phpstan              - check source code with PhpStan"
	@echo "lint-rector               - check source code with Rector"
	@echo "lint-rector-fix           - 🪄 fix source code with Rector"
	@echo ""
	@echo "🐘 Work with PHP dev-server:"
	@echo "php-dev-server-up         - run PHP dev-server on 10888 port and expose «public» folder"
	@echo "php-dev-server-down       - shutdown PHP dev-server"
	@echo ""
	@echo "🌎 Work with tunnels to internet (ngrok):"
	@echo "ngrok-up                  - run ngrok and expose 10888 port to public internet address"
	@echo "ngrok-down                - shutdown ngrok"
	@echo ""
	@echo "🚀 Work with application:"
	@echo "php-cli-app               - run container php-cli and run cli application «b24:test-command»"
	@echo "php-cli-bash              - run container php-cli and open shell with arguments"
	@echo ""
	@echo "🎓 Examples"
	@echo "If you want build docker containers, put this command in shell:"
	@echo "make docker-build"

# init project
.PHONY: docker-build
docker-build:
	$(DOCKER_COMPOSE) build

.PHONY: docker-rebuild
docker-rebuild:
	$(DOCKER_COMPOSE) rm -vsf ;\
	$(DOCKER_COMPOSE) down -v --remove-orphans ;\
	make docker-build ;\
	make docker-up

.PHONY: docker-up
docker-up:
	$(DOCKER_COMPOSE) up -d

.PHONY: docker-down
docker-down:
	$(DOCKER_COMPOSE) down --remove-orphans

.PHONY: docker-down-clear
docker-down-clear:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

.PHONY: php-cli-bash
php-cli-bash:
	$(DOCKER_COMPOSE) run --rm php-cli sh $(filter-out $@,$(MAKECMDGOALS))

.PHONY: php-cli-app
php-cli-app:
	$(DOCKER_COMPOSE) run --rm php-cli php bin/console b24:test-command

.PHONY: composer-install
composer-install:
	$(DOCKER_COMPOSE) run --rm php-cli composer install

.PHONY: composer-update
composer-update:
	$(DOCKER_COMPOSE) run --rm php-cli composer update

.PHONY: composer-dumpautoload
composer-dumpautoload:
	$(DOCKER_COMPOSE) run --rm php-cli composer dumpautoload

# call composer with any parameters
# make composer install
# make composer "install --no-dev"
.PHONY: composer
composer:
	$(DOCKER_COMPOSE) run --rm php-cli composer $(filter-out $@,$(MAKECMDGOALS))

# linters
.PHONY: lint-allowed-licenses
lint-allowed-licenses:
	docker-compose run --rm php-cli vendor/bin/composer-license-checker

.PHONY: lint-cs-fixer
lint-cs-fixer:
	docker-compose run --rm php-cli vendor/bin/php-cs-fixer check --verbose --diff

.PHONY: lint-cs-fixer-fix
lint-cs-fixer-fix:
	docker-compose run --rm php-cli vendor/bin/php-cs-fixer fix --verbose --diff

.PHONY: lint-phpstan
lint-phpstan:
	docker-compose run --rm php-cli vendor/bin/phpstan --memory-limit=2G analyse -vvv

.PHONY: lint-rector
lint-rector:
	docker-compose run --rm php-cli vendor/bin/rector process --dry-run

.PHONY: lint-rector-fix
lint-rector-fix:
	docker-compose run --rm php-cli vendor/bin/rector process

# work dev environment
.PHONY: php-dev-server-up
php-dev-server-up:
	docker-compose run --rm -p 10888:10888 php-cli php -S 0.0.0.0:10888 -t public

.PHONY: php-dev-server-down
php-dev-server-down:
	docker-compose down --remove-orphans

# work with tunnels for dev env
.PHONY: ngrok-up
ngrok-up:
	ngrok http 127.0.0.1:10888

.PHONY: ngrok-down
ngrok-down:
	@pids=$$(ps aux | grep ' ngrok http' | grep -v 'grep' | awk '{print $$2}'); \
	if [ -n "$$pids" ]; then \
		echo "Killing process(es): $$pids"; \
		echo "$$pids" | xargs kill; \
	else \
		echo "No ngrok process found."; \
	fi
