# Checklist: RabbitMQ + Messenger для `ActivityHandler`

## Подготовка
- [x] Уточнить и зафиксировать `.env` переменные для RabbitMQ (`host`, `port`, `user`, `pass`, `vhost` если нужен).
- [x] Добавить в `composer.json` зависимости `symfony/messenger`, `symfony/amqp-messenger`, `symfony/dotenv`.
- [x] Обновить зависимости и проверить, что autoload/lock-файл консистентны.

## RabbitMQ и окружение
- [x] Добавить сервис `rabbitmq` в `docker-compose.yaml`.
- [x] Прописать порты `5672` и `15672`.
- [x] Добавить volume для данных RabbitMQ.
- [x] Прокинуть переменные окружения RabbitMQ в `php-cli`.
- [x] Добавить команды в `Makefile` для запуска одного worker.
- [x] Добавить команду в `Makefile` для запуска двух worker-процессов.
- [x] Добавить helper для открытия/показа RabbitMQ management URL.

## Messenger wiring
- [x] Добавить сервисы Messenger в текущий DI bootstrap без `FrameworkBundle`.
- [x] Создать transport `activity`.
- [x] Создать failure transport `activity_failed`.
- [x] Настроить routing `ActivityMessage -> activity`.
- [x] Настроить retry policy на 3 попытки с фиксированным backoff.
- [x] Настроить handler locator и message bus.
- [x] Настроить worker runtime для кастомной console-команды.

## DTO и маппинг
- [x] Создать `App\Message\ActivityMessage`.
- [x] Зафиксировать плоскую структуру `auth`.
- [x] Зафиксировать плоскую структуру `workflowDocumentId`.
- [x] Зафиксировать плоскую структуру `workflowDocumentType`.
- [x] Создать mapper `ActivityRequest -> ActivityMessage`.
- [x] Создать mapper `ActivityMessage -> ActivityRequest` или execution context.
- [x] Проверить, что в message не попадают объекты SDK.

## Bitrix24 context
- [x] Добавить в `Bitrix24ServiceBuilderFactory` метод `createFromWorkflowAuth(array $authPayload): ServiceBuilder`.
- [x] Реализовать восстановление service builder только по auth из сообщения.
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
- [x] Оставить `ActivityHandler` как синхронный сервис исполнения activity.
- [x] Убрать из него зависимость на HTTP endpoint flow.
- [x] Сделать так, чтобы `ActivityHandler` возвращал `ActivityResponse`.
- [x] Оставить маршрутизацию по `ActivityHandlerInterface` внутри `ActivityHandler`.
- [x] Сделать отсутствие handler ошибкой с исключением.

## Worker handler и команда
- [x] Создать `App\MessageHandler\ActivityMessageHandler`.
- [x] Восстанавливать request/context из `ActivityMessage`.
- [x] Вызывать `ActivityHandler` из message handler.
- [x] После успешного результата вызывать `bizproc event()->send(...)`.
- [x] При любой ошибке пробрасывать исключение для retry/failure transport.
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
- [x] Написать unit test на mapping `ActivityRequest -> ActivityMessage`.
- [x] Написать unit test на обратное восстановление context из `ActivityMessage`.
- [x] Написать unit test на выбор handler по `code`.
- [x] Написать unit test на ошибку при отсутствии handler.
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
