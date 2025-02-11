# This file is part of the B24PhpSdk package.
#
#  For the full copyright and license information, please view the MIT-LICENSE.txt
#  file that was distributed with this source code.

default:
	@echo "make needs target:"
	@egrep -e '^\S+' ./Makefile | grep -v default | sed -r 's/://' | sed -r 's/^/ - /'

# init project
.PHONY: init
init:
	@echo "remove all containers"
	docker-compose down --remove-orphans
	@echo "build containers"
	docker-compose build
	@echo "install dependencies"
	docker-compose run --rm php-cli composer install
	@echo "change owner of var folder for access from container"
    docker-compose run --rm php-cli chown -R www-data:www-data /var/www/html/var/
	@echo "run application…"
	docker-compose up -d

#
.PHONY: php-cli-bash
php-cli-bash:
	docker-compose run --rm php-cli sh $(filter-out $@,$(MAKECMDGOALS))

# work dev environment
.PHONY: up-php-dev-server
up-php-dev-server:
	docker-compose run --rm -p 10080:10080 php-cli php -S 0.0.0.0:10080 -t public

.PHONY: down-php-dev-server
down-php-dev-server:
	docker-compose down --remove-orphans

.PHONY: up-ngrok
up-ngrok:
	ngrok http 127.0.0.1:10080

.PHONY: down-ngrok
stop-ngrok:
	@pids=$$(ps aux | grep ' ngrok http' | grep -v 'grep' | awk '{print $$2}'); \
	if [ -n "$$pids" ]; then \
		echo "Killing process(es): $$pids"; \
		echo "$$pids" | xargs kill; \
	else \
		echo "No ngrok process found."; \
	fi






