# План внедрения RabbitMQ и Symfony Messenger для асинхронного `ActivityHandler`

## Кратко
Переводим обработку activity на схему `Bitrix24 -> HTTP endpoint -> RabbitMQ -> 2 background worker -> activity handler -> BizProc event()->send()`.

Цель v1:
- входящий HTTP-запрос от Bitrix24 больше не выполняет бизнес-логику синхронно;
- он только валидирует вход, публикует сообщение в очередь и быстро отвечает `200 OK`;
- два одинаковых worker-процесса читают одну очередь и выполняют activity в фоне;
- после успешной обработки worker сам отправляет ответ в Bitrix24 через `event()->send(...)`.

## Архитектурные решения
- Не использовать `FrameworkBundle`.
- Не использовать `config/packages/*`.
- Messenger встраивается как набор компонентов поверх текущего DI-контейнера в `src/DI/DI.php`.
- Не использовать встроенную команду `messenger:consume`.
- Добавить собственную консольную команду `app:activity-worker`, зарегистрированную в `bin/console`.
- Использовать одну очередь для всех activity.
- Использовать два одинаковых worker-процесса этой очереди.
- Маршрутизацию по `activity code` оставить внутри PHP, как и сейчас в `src/Workflow/Activities/ActivityHandler.php`.
- Для ошибок использовать только Messenger failure transport.
- Broker-level DLX/DLQ RabbitMQ в v1 не настраивать отдельно.
- Семантика доставки: `at-least-once`.
- Deduplication в v1 не делать.
- Порядок обработки сообщений одного `workflowId` не гарантируется и считается допустимым.

## Очереди и именование
- RabbitMQ exchange: `activity`
- Основная очередь: `activity_queue`
- Очередь ошибок Messenger failure transport: `activity_failed`
- Routing key основной очереди: `activity`
- Routing key failure transport: `activity_failed`

## Изменения в коде
### 1. HTTP endpoint
Изменить `public/crm-activity-handler.php`:
- оставить только приём `Request::createFromGlobals()`;
- собрать `IncomingWorkflowRequest`;
- собрать `ActivityRequest`;
- преобразовать `ActivityRequest` в serializable message DTO;
- отправить message в Messenger;
- вернуть `200 OK` с пустым телом.

Правила ответа:
- успешная публикация в очередь: `200 OK`, пустое тело;
- невалидный входящий запрос: `500`;
- ошибка публикации в RabbitMQ: `500`;
- синхронный вызов `event()->send(...)` из HTTP endpoint запрещён;
- синхронный вызов бизнес-логики activity из HTTP endpoint запрещён.

### 2. Message DTO
Добавить новый DTO `App\Message\ActivityMessage`.

Он должен содержать только скаляры и массивы:
- `workflowId`
- `code`
- `eventToken`
- `properties`
- `isUseSubscription`
- `timeoutDuration`
- `timestamp`
- `auth`
- `workflowDocumentId`
- `workflowDocumentType`

Структура полей:
- `auth` хранить как плоский массив с теми полями, которые нужны для восстановления SDK auth-контекста;
- `workflowDocumentId` хранить как массив скаляров;
- `workflowDocumentType` хранить как массив скаляров.

Запрещено:
- передавать в очередь объекты SDK;
- полагаться на PHP object serialization для SDK request/auth/document objects.

### 3. Mapper
Добавить mapper/factory:
- `ActivityRequest -> ActivityMessage`
- `ActivityMessage -> ActivityRequest` или `ActivityExecutionContext`

Для v1 использовать только ручной mapping.
`symfony/serializer` не использовать в runtime-цепочке обработки сообщения.

### 4. Worker handler
Добавить `App\MessageHandler\ActivityMessageHandler`.

Его обязанности:
- принять `ActivityMessage`;
- восстановить внутренний request/context;
- вызвать текущую маршрутизацию activity handlers;
- получить `ActivityResponse`;
- выполнить `bizproc event()->send(...)`;
- пробросить исключение наружу при любой ошибке, чтобы Messenger применил retry/failure policy.

### 5. Сервис выполнения activity
Текущий `src/Workflow/Activities/ActivityHandler.php` оставить как синхронный сервис доменного уровня, но изменить его ответственность:
- он больше не вызывается из HTTP endpoint;
- он вызывается только из `ActivityMessageHandler`;
- внутри него остаётся поиск нужного `ActivityHandlerInterface` по `code`;
- внутри него остаётся вызов конкретного handler;
- отправка `event()->send(...)` может остаться здесь, если handler возвращает `ActivityResponse`, либо быть вынесена в `ActivityMessageHandler`;
- выбрать один вариант и использовать его везде:
- рекомендованный вариант: `ActivityHandler` возвращает `ActivityResponse`, а `ActivityMessageHandler` уже отправляет `event()->send(...)`.

Для v1 зафиксировать рекомендованный вариант:
- `ActivityHandler` только исполняет activity и возвращает `ActivityResponse`;
- `ActivityMessageHandler` отвечает за интеграционный шаг `event()->send(...)`.

### 6. Bitrix24 auth/context
Расширить `Bitrix24ServiceBuilderFactory` новым методом:
- `createFromWorkflowAuth(array $authPayload): ServiceBuilder`

Правила:
- worker обязан использовать только auth из `ActivityMessage`;
- fallback на `createFromStoredToken()` в worker запрещён;
- `createFromStoredToken()` остаётся только для CLI-операций установки/диагностики;
- формат `authPayload` должен полностью покрывать данные, необходимые для создания service builder в контексте конкретного входящего activity.

### 7. Messenger wiring
Добавить в DI сервисы для:
- message bus;
- AMQP transport `activity`;
- failure transport `activity_failed`;
- sender/receiver;
- locator для message handlers;
- retry strategy;
- worker runtime.

Конфигурацию хранить в существующем сервисном bootstrap, а не в Symfony package config.

### 8. Консольная команда worker
Добавить команду `app:activity-worker`.

Поведение команды:
- поднимает consumer для transport `activity`;
- обрабатывает сообщения бесконечно;
- логирует start/finish/error;
- корректно завершается по SIGTERM/SIGINT;
- использует Messenger retry и failure transport.

В `bin/console` зарегистрировать:
- `TestCommand`
- `ActivityWorkerCommand`

## Docker и запуск
### docker-compose
В `docker-compose.yaml` добавить:
- сервис `rabbitmq`
- management port `15672`
- AMQP port `5672`
- volume для данных RabbitMQ
- env для `RABBITMQ_DEFAULT_USER` и `RABBITMQ_DEFAULT_PASS`

Отдельные контейнеры worker в v1 не добавлять.
Workers запускаются отдельными командами через существующий `php-cli` образ.

### Makefile
В `Makefile` добавить команды:
- `activity-worker` для запуска одного worker
- `activity-workers-up` для запуска двух worker-процессов
- `rabbitmq-ui` или краткую helper-цель для вывода URL management UI

`activity-workers-up` должен запускать ровно 2 одинаковых consumer-процесса.

## Retry и failure policy
Политика ошибок:
- retries: 3 попытки
- backoff: фиксированный, например 1000 мс, 5000 мс, 10000 мс
- после исчерпания retry сообщение переводится в `activity_failed`
- worker логирует каждую попытку
- сообщение из `activity_failed` не переобрабатывается автоматически

Что считается ошибкой:
- не найден handler по `code`
- исключение внутри конкретного activity handler
- ошибка создания Bitrix24 service builder
- ошибка `event()->send(...)`

Во всех этих случаях:
- worker бросает исключение;
- ack успешной обработки не отправляется;
- Messenger сам применяет retry/failure routing.

## Логирование
Во всех точках логировать поля:
- `workflowId`
- `code`
- `eventToken`
- `messageId` если доступен
- `attempt` для retries

Обязательные события логов:
- HTTP request accepted
- message published
- worker received message
- handler resolved
- activity completed
- bitrix event sent
- retry scheduled
- moved to failure transport

## Ограничения и допущения
- v1 не гарантирует порядок сообщений одного workflow.
- v1 не реализует deduplication.
- v1 допускает повторную обработку одного и того же activity при повторной доставке.
- `timeoutDuration` из входящего запроса в v1 только переносится в message и логируется.
- worker не отменяет задачу по `timeoutDuration`.
- если Bitrix24 или RabbitMQ временно недоступны, используется retry policy; ручной recovery из `activity_failed` выполняется оператором.

## Тест-план
### Unit
- `ActivityRequest -> ActivityMessage` маппится без потери данных.
- `ActivityMessage -> execution context` корректно восстанавливает данные.
- `ActivityHandler` выбирает правильный `ActivityHandlerInterface` по `code`.
- отсутствие handler вызывает исключение.
- `Bitrix24ServiceBuilderFactory::createFromWorkflowAuth()` строит builder из auth payload.
- `ActivityMessageHandler` вызывает `event()->send(...)` только после успешного выполнения handler.

### Integration
- HTTP endpoint публикует сообщение и возвращает `200`.
- при ошибке публикации endpoint возвращает `500`.
- worker читает сообщение из `activity_queue`.
- worker вызывает нужный activity handler.
- при успехе выполняется `event()->send(...)`.
- при исключении происходит 3 retry.
- после исчерпания retry сообщение попадает в `activity_failed`.

### Поведенческие сценарии
- два worker одновременно обрабатывают разные сообщения одной очереди.
- duplicate delivery приводит к повторной обработке и это считается допустимым поведением v1.
- если worker падает до ack, сообщение может быть доставлено повторно.
- если `event()->send(...)` падает, сообщение не считается успешно обработанным и уходит в retry.
- если handler не найден, сообщение попадает в failure transport.

## Изменения зависимостей
Добавить в `composer.json`:
- `symfony/messenger`
- `symfony/amqp-messenger`
- `symfony/dotenv`

Не добавлять в v1:
- `symfony/serializer`
- `FrameworkBundle`

## Итог для исполнителя
Исполнитель должен реализовать ровно такую цепочку:
1. `public/crm-activity-handler.php` публикует `ActivityMessage` и отвечает `200`.
2. `app:activity-worker` читает `activity_queue`.
3. `ActivityMessageHandler` восстанавливает контекст, вызывает `ActivityHandler`, получает `ActivityResponse` и отправляет `event()->send(...)`.
4. При любой ошибке срабатывает retry.
5. После 3 неудач сообщение попадает в `activity_failed`.
6. Для обработки запускаются два одинаковых worker-процесса.
