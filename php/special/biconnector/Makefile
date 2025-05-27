PROJECT_NAME=bitrix24-biconnector-demo
DC=docker-compose

.PHONY: help build up down stop restart logs bash clean prune init-db

help:
	@echo "Usage: make [target]"
	@echo ""
	@echo "Production targets:"
	@echo "  build      -- Build containers"
	@echo "  up         -- Start app (in background)"
	@echo "  down       -- Stop and remove containers"
	@echo "  bash       -- Bash into container"
	@echo "  logs       -- Show logs"
	@echo ""
	@echo "Database targets:"
	@echo "  init-db    -- Initialize the SQLite dev database from schema.sql"
	@echo ""
	@echo "Other:"
	@echo "  clean      -- Remove all containers, volumes, images"
	@echo "  prune      -- Clean all Docker system junk (careful!)"

# Production
build:
	$(DC) build --no-cache

up:
	$(DC) up -d

down:
	$(DC) down

bash:
	$(DC) exec app bash

logs:
	$(DC) logs -f

# Database
init-db:
	@echo "Initializing SQLite database from schema.sql..."
	@mkdir -p ./data
	@rm -f ./data/demo.db
	@sqlite3 ./data/demo.db < ./sql/schema.sql
	@echo "Database ./data/demo.db created!"

# Maintenance
clean: down down-dev
	docker system prune -af --volumes

prune:
	docker system prune -af --volumes