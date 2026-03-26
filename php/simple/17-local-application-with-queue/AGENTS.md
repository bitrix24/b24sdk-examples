# AGENTS.md

## Назначение

Этот файл является точкой входа для LLM в проект. Перед любыми изменениями его нужно прочитать.

Назначение проекта: локальное приложение Bitrix24 на PHP с workflow-активити, заготовками под роботов, Docker-окружением и RabbitMQ как основой для асинхронной обработки.

## Язык коммуникации

Вся коммуникация по проекту, комментарии для агента и итоговые ответы должны быть на русском языке.

## Работа с чеклистами

После завершения задачи нужно обязательно отмечать выполненные пункты в связанных чеклистах, чтобы прогресс по задаче был явно виден.

## Жесткое правило: все операции только через `make`

Все операционные действия должны выполняться через `make`.

- Используй `make <target>` для сборки, запуска, остановки, composer-операций, линтеров, локального сервера, ngrok и воркеров.
- Не вызывай напрямую `docker compose`, `docker-compose`, `composer` или `php bin/console`, если для этого уже есть эквивалентный target в `Makefile`.
- Если нужной операции еще нет в `Makefile`, сначала добавь target в [`Makefile`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/Makefile), затем запускай ее через `make`.

Примеры:

- `make`
- `make docker-build`
- `make docker-up`
- `make composer-install`
- `make lint-phpstan`
- `make php-cli-app`

## Что есть в проекте

- Docker-окружение: [`docker-compose.yaml`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/docker-compose.yaml)
  В проекте определены сервисы `php-cli` и `rabbitmq`.
- Основная поверхность команд: [`Makefile`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/Makefile)
- Зависимости и пакеты: [`composer.json`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/composer.json)
- Настройка DI-контейнера: [`config/services.yaml`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/config/services.yaml)
- CLI entrypoint: [`bin/console`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/bin/console)
- HTTP entrypoints:
  [`public/index.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/index.php),
  [`public/install.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/install.php),
  [`public/event-handler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/event-handler.php),
  [`public/crm-activity-handler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/crm-activity-handler.php)

## Runtime и архитектура

### Загрузка и инициализация

- Автозагрузка построена по PSR-4 с namespace `App\\` из каталога [`src/`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src).
- DI собирается вручную в [`src/DI/DI.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/DI/DI.php) на основе [`config/services.yaml`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/config/services.yaml).
- `LoggerInterface` резолвится через [`src/LoggerFactory.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/LoggerFactory.php).

### HTTP flow

- [`public/install.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/install.php) обрабатывает установку приложения.
- [`public/index.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/index.php) обслуживает placement UI flow.
- [`public/event-handler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/event-handler.php) принимает события Bitrix24.
- [`public/crm-activity-handler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/public/crm-activity-handler.php) принимает вызовы workflow-активити, собирает `ActivityRequest` и передает управление в `ActivityHandler`.

### Активити и роботы

- Обработчики активити лежат в [`src/Workflow/Activities/`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Workflow/Activities).
- Обработчики роботов лежат в [`src/Robots/`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Robots).
- Хендлеры автоматически регистрируются через resource patterns и tags в [`config/services.yaml`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/config/services.yaml).
- Маршрутизация идет по коду:
  [`src/Workflow/Activities/ActivityHandler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Workflow/Activities/ActivityHandler.php) выбирает обработчик активити по коду активити.
  [`src/Robots/RobotHandler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Robots/RobotHandler.php) выбирает обработчик робота по коду робота.

### Важное текущее ограничение

RabbitMQ уже присутствует в инфраструктуре, но асинхронный queue-flow в текущем дереве исходников реализован не полностью.

- В [`src/Workflow/Activities/ActivityHandler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Workflow/Activities/ActivityHandler.php) все еще есть `todo add to rabbit MQ`.
- В [`src/Robots/RobotHandler.php`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Robots/RobotHandler.php) тоже остались заглушки под очередь.
- В [`Makefile`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/Makefile) есть targets `activity-worker` и фоновые worker targets, но команда `app:activity-worker` в текущем наборе регистрации `bin/console` не определена.

Нельзя считать queue worker flow готовым. Перед развитием этой части нужно сначала проверить фактическую реализацию.

## Как работать с репозиторием

### Базовые команды

- Показать доступные операции: `make`
- Собрать контейнеры: `make docker-build`
- Поднять окружение: `make docker-up`
- Остановить окружение: `make docker-down`
- Установить зависимости: `make composer-install`
- Запустить CLI demo-команду: `make php-cli-app`
- Поднять локальный PHP server: `make php-dev-server-up`

### Quality gates

После изменений запускай проверки через `make`:

- `make lint-cs-fixer`
- `make lint-phpstan`
- `make lint-rector`
- `make lint-allowed-licenses`

Если изменение требует автоисправлений, используй соответствующий `*-fix` target тоже через `make`.

## Конвенции изменений для агентов

- Предпочитай минимальные и локальные изменения.
- Сохраняй текущую разбивку по доменам: `Controller`, `Events`, `Repository`, `Workflow/Activities`, `Robots`.
- Для новых workflow-активити придерживайся существующего шаблона из трех файлов: `Handler.php`, `Properties.php`, `Result.php`.
- Для новых роботов повторяй структуру, уже используемую в [`src/Robots/`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/src/Robots).
- Используй DI autowiring и tagged handlers вместо ручных реестров.
- Поддерживай полезное логирование; проект уже логирует входящие запросы и выполнение хендлеров.

## Окружение и состояние

- Базовые переменные загружаются из `.env`, локальные переопределения из `.env.local`.
- Логи пишутся в [`var/logs/`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/var/logs).
- Репозиторий может быть в dirty state. Не откатывай несвязанные пользовательские изменения.

## Источники истины

- Операционный workflow: [`Makefile`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/Makefile)
- Человеко-ориентированное описание: [`README.md`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/README.md)
- Wiring сервисов: [`config/services.yaml`](/Users/mesilov/work/Bitrix24/b24sdk-examples/php/simple/17-local-application-with-queue/config/services.yaml)

Если этот документ расходится с фактическим кодом, сначала доверяй коду, затем обновляй этот файл.
