# Checklist: RabbitMQ + Messenger для `ActivityHandler`

## Подготовка
- [ ] Уточнить и зафиксировать `.env` переменные для RabbitMQ (`host`, `port`, `user`, `pass`, `vhost` если нужен).
- [ ] Добавить в `composer.json` зависимости `symfony/messenger`, `symfony/amqp-messenger`, `symfony/dotenv`.
- [ ] Обновить зависимости и проверить, что autoload/lock-файл консистентны.

## RabbitMQ и окружение
- [ ] Добавить сервис `rabbitmq` в `docker-compose.yaml`.
- [ ] Прописать порты `5672` и `15672`.
- [ ] Добавить volume для данных RabbitMQ.
- [ ] Прокинуть переменные окружения RabbitMQ в `php-cli`.
- [ ] Добавить команды в `Makefile` для запуска одного worker.
- [ ] Добавить команду в `Makefile` для запуска двух worker-процессов.
- [ ] Добавить helper для открытия/показа RabbitMQ management URL.

## Messenger wiring
- [ ] Добавить сервисы Messenger в текущий DI bootstrap без `FrameworkBundle`.
- [ ] Создать transport `activity`.
- [ ] Создать failure transport `activity_failed`.
- [ ] Настроить routing `ActivityMessage -> activity`.
- [ ] Настроить retry policy на 3 попытки с фиксированным backoff.
- [ ] Настроить handler locator и message bus.
- [ ] Настроить worker runtime для кастомной console-команды.

## DTO и маппинг
- [ ] Создать `App\Message\ActivityMessage`.
- [ ] Зафиксировать плоскую структуру `auth`.
- [ ] Зафиксировать плоскую структуру `workflowDocumentId`.
- [ ] Зафиксировать плоскую структуру `workflowDocumentType`.
- [ ] Создать mapper `ActivityRequest -> ActivityMessage`.
- [ ] Создать mapper `ActivityMessage -> ActivityRequest` или execution context.
- [ ] Проверить, что в message не попадают объекты SDK.

## Bitrix24 context
- [ ] Добавить в `Bitrix24ServiceBuilderFactory` метод `createFromWorkflowAuth(array $authPayload): ServiceBuilder`.
- [ ] Реализовать восстановление service builder только по auth из сообщения.
- [ ] Проверить, что worker не использует `createFromStoredToken()`.

## HTTP endpoint
- [ ] Переделать `public/crm-activity-handler.php` в thin-controller.
- [ ] Оставить только parse входящего запроса и публикацию message.
- [ ] Удалить прямой вызов синхронной обработки activity из endpoint.
- [ ] Возвращать `200 OK` при успешной публикации.
- [ ] Возвращать `500` при ошибке валидации/parsing запроса.
- [ ] Возвращать `500` при ошибке публикации в RabbitMQ.
- [ ] Добавить логирование `workflowId`, `code`, `eventToken` при приёме и публикации.

## Доменная обработка
- [ ] Оставить `ActivityHandler` как синхронный сервис исполнения activity.
- [ ] Убрать из него зависимость на HTTP endpoint flow.
- [ ] Сделать так, чтобы `ActivityHandler` возвращал `ActivityResponse`.
- [ ] Оставить маршрутизацию по `ActivityHandlerInterface` внутри `ActivityHandler`.
- [ ] Сделать отсутствие handler ошибкой с исключением.

## Worker handler и команда
- [ ] Создать `App\MessageHandler\ActivityMessageHandler`.
- [ ] Восстанавливать request/context из `ActivityMessage`.
- [ ] Вызывать `ActivityHandler` из message handler.
- [ ] После успешного результата вызывать `bizproc event()->send(...)`.
- [ ] При любой ошибке пробрасывать исключение для retry/failure transport.
- [ ] Создать console-команду `app:activity-worker`.
- [ ] Зарегистрировать `ActivityWorkerCommand` в `bin/console`.
- [ ] Обработать graceful shutdown по SIGTERM/SIGINT.

## Логирование и наблюдаемость
- [ ] Логировать `workflowId`, `code`, `eventToken`, `attempt`.
- [ ] Логировать событие `message published`.
- [ ] Логировать событие `worker received message`.
- [ ] Логировать событие `handler resolved`.
- [ ] Логировать событие `activity completed`.
- [ ] Логировать событие `bitrix event sent`.
- [ ] Логировать событие `retry scheduled`.
- [ ] Логировать событие `moved to failure transport`.

## Тесты
- [ ] Написать unit test на mapping `ActivityRequest -> ActivityMessage`.
- [ ] Написать unit test на обратное восстановление context из `ActivityMessage`.
- [ ] Написать unit test на выбор handler по `code`.
- [ ] Написать unit test на ошибку при отсутствии handler.
- [ ] Написать unit test на `createFromWorkflowAuth()`.
- [ ] Написать integration test: endpoint публикует сообщение и отвечает `200`.
- [ ] Написать integration test: endpoint отвечает `500` при ошибке публикации.
- [ ] Написать integration test: worker читает сообщение из `activity_queue`.
- [ ] Написать integration test: успешный handler вызывает `event()->send(...)`.
- [ ] Написать integration test: после 3 неудач сообщение попадает в `activity_failed`.
- [ ] Проверить сценарий duplicate delivery как допустимое поведение v1.
- [ ] Проверить сценарий падения worker до ack и повторной доставки.

## Ручная приёмка
- [ ] Поднять `rabbitmq` и `php-cli`.
- [ ] Запустить 2 worker-процесса.
- [ ] Отправить тестовый activity из Bitrix24.
- [ ] Убедиться, что HTTP endpoint отвечает быстро.
- [ ] Убедиться, что сообщение попадает в `activity_queue`.
- [ ] Убедиться, что один из worker обрабатывает сообщение.
- [ ] Убедиться, что `event()->send(...)` уходит из worker.
- [ ] Смоделировать ошибку handler и проверить retries.
- [ ] Проверить появление сообщения в `activity_failed` после исчерпания retries.
