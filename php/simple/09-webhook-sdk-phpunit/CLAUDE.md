# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Bitrix24 PHP SDK example demonstrating PHPUnit testing with a contact scoring system. The application scores contacts based on risk levels (low, medium, high) using an external scoring model. The scored data is stored in a Bitrix24 CRM via REST API webhooks.

**Note:** This is an educational example and should NOT be used in production.

## Development Environment

All code runs inside Docker containers. The project uses `docker compose` and commands are orchestrated via `Makefile`.

### Initial Setup

1. Create `.env.local` with your Bitrix24 webhook URL:
   ```
   BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL=https://your-bitrix24-portal-url
   ```

2. Build containers and install dependencies:
   ```shell
   make docker-build
   make composer-install
   ```

3. Install Bitrix24 entities (smart process for risk levels, custom field for score):
   ```shell
   make php-cli-app-install
   ```

## Common Commands

### Docker Operations
- `make docker-build` - Build containers
- `make docker-up` - Start containers
- `make docker-down` - Stop containers
- `make docker-rebuild` - Full rebuild (removes volumes)

### Dependency Management
- `make composer-install` - Install dependencies
- `make composer-update` - Update dependencies
- `make composer-dumpautoload` - Regenerate autoload

### Testing
- `make test-unit` - Run unit tests via PHPUnit
- Tests are configured in `phpunit.xml.dist` with two suites:
  - `unit_tests` - Tests in `tests/Unit/`
  - `integration_tests` - Tests in `tests/Integration/`

### Code Quality
- `make lint-cs-fixer` - Check code style (PHP CS Fixer)
- `make lint-cs-fixer-fix` - Auto-fix code style
- `make lint-phpstan` - Static analysis (PHPStan)
- `make lint-rector` - Check for code improvements (Rector)
- `make lint-rector-fix` - Auto-apply Rector fixes
- `make lint-allowed-licenses` - Verify dependency licenses

### Application Commands
- `make php-cli-bash` - Open shell in php-cli container
- `make php-cli-app-process-contact` - Process contact scoring
- Inside container: `php bin/console` to see available CLI commands

## Architecture

### Domain Model: Contact Risk Scoring

**Business Context:**
- Contacts are scored numerically (1-20) by an external system
- Scores are categorized into risk levels:
  - Low: ≤ 5
  - Medium: 6-9
  - High: ≥ 10
- Risk levels are stored as a Smart Process in Bitrix24
- Contact entities have a custom integer field storing the current score

### Code Structure

**Dependency Injection:**
- Uses Symfony DI Container configured in `config/services.yaml`
- Container is accessed via singleton `App\DI\DI::get(string $id)`
- All services are public and autowired

**Core Scoring Domain (`src/Scoring/`):**
- `Score` - Value object representing score (1-20) and associated RiskLevel enum
- `RiskLevel` - Enum with three cases: LOW, MEDIUM, HIGH
- `Services/ScoringProcessor` - Main service orchestrating scoring logic
- `Services/ScoringModelInterface` - Interface for scoring strategies
- `Services/DefaultScoringModel` - Default implementation of scoring model

**Bitrix24 Integration (`src/Scoring/Infrastructure/Bitrix24/`):**
- `ScoreInstaller` / `ScoreQueries` / `ScoreCommands` - Manage contact score field
- `RiskLevels/RiskLevelInstaller` - Creates Smart Process with risk level items
- `RiskLevels/RiskLevelQueries` / `RiskLevelMapper` - Query and map risk levels
- `ScoreFieldMapper` - Maps domain Score to Bitrix24 field values

**CLI Commands (`src/Commands/`):**
- `InstallCommand` - Sets up Bitrix24 entities (b24:install)
- `ScoreContactCommand` - Processes contact scoring (b24:process-contact)
- Commands are registered in `bin/console`

**Logging:**
- PSR-3 Monolog logger configured via `LoggerFactory`
- Logs to `var/logs/` directory
- Log level controlled by `BITRIX24_PHP_SDK_LOG_LEVEL` in `.env`

### Testing

PHPUnit 12 is configured with separate unit and integration test suites. Unit tests should not depend on external services. Tests use PSR-4 autoloading from `tests/` directory with namespace `App\Tests\`.

Bootstrap file: `tests/bootstrap.php`

## Key Dependencies

- `bitrix24/b24phpsdk` (dev-dev) - Bitrix24 REST API SDK
- `symfony/console`, `symfony/dependency-injection`, `symfony/config` - Framework components
- `monolog/monolog` - PSR-3 logging
- `phpunit/phpunit` - Testing framework
- `league/csv`, `fakerphp/faker` - Data handling utilities

## Configuration Files

- `.php-cs-fixer.php` - PHP CS Fixer rules
- `phpstan.neon.dist` - PHPStan static analysis config
- `rector.php` - Rector refactoring rules
- `.allowed-licenses.php` - Whitelisted dependency licenses
- `docker-compose.yaml` - Container orchestration