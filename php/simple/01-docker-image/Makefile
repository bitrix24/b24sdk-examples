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
	@echo "------------------------------"
	@echo "Bitrix24 PHP SDK examples     "
	@echo "01 — default makefile example "
	@echo "------------------------------"
	@echo ""
	@echo "docker-build              - build containers"
	@echo "docker-up                 - run docker"
	@echo "docker-down               - stop docker"
	@echo "docker-down-clear         - stop docker and remove orphaned containers"
	@echo "php-cli-bash              - run container php-cli and open shell with arguments"
	@echo ""

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
