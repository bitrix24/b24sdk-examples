# Implementation Order: RabbitMQ + Messenger для `ActivityHandler`

## 1. Подготовить окружение
1. Добавить composer-зависимости для Messenger и AMQP.
2. Добавить RabbitMQ в `docker-compose.yaml`.
3. Прокинуть RabbitMQ env в `php-cli`.
4. Добавить make-команды для запуска worker и RabbitMQ UI.

Результат этапа:
- проект собирается с новыми зависимостями;
- RabbitMQ поднимается локально;
- у приложения есть все env для подключения к брокеру.

## 2. Подготовить инфраструктуру Messenger
1. Собрать transport `activity`.
2. Собрать failure transport `activity_failed`.
3. Настроить message bus, routing и retry policy.
4. Подключить всё это в текущий DI bootstrap без `FrameworkBundle`.

Результат этапа:
- из контейнера можно получить готовый bus, sender, receiver и worker runtime.

## 3. Зафиксировать формат сообщения
1. Создать `ActivityMessage`.
2. Создать ручной mapper `ActivityRequest -> ActivityMessage`.
3. Создать ручной mapper `ActivityMessage -> execution context`.
4. Проверить, что в message только массивы и скаляры.

Результат этапа:
- payload очереди стабилен и сериализуем;
- worker не зависит от сериализации SDK-объектов.

## 4. Подготовить Bitrix24 context для worker
1. Добавить `createFromWorkflowAuth(array $authPayload): ServiceBuilder`.
2. Реализовать создание service builder из auth-полей сообщения.
3. Явно исключить использование `createFromStoredToken()` внутри worker flow.

Результат этапа:
- worker выполняет activity строго в контексте исходного входящего вызова.

## 5. Переделать HTTP endpoint
1. Упростить `public/crm-activity-handler.php` до thin-controller.
2. Оставить parse входящего запроса и публикацию в bus.
3. Удалить прямой вызов `ActivityHandler`.
4. Вернуть `200` на успешный publish и `500` на ошибки.

Результат этапа:
- endpoint отвечает быстро и ничего не исполняет синхронно.

## 6. Переделать доменную обработку
1. Оставить `ActivityHandler` синхронным доменным сервисом.
2. Сделать его ответственным только за выбор конкретного activity handler и получение `ActivityResponse`.
3. Убрать из него интеграционный шаг `event()->send(...)`.

Результат этапа:
- доменный слой отделён от транспорта и внешней доставки результата.

## 7. Реализовать worker
1. Создать `ActivityMessageHandler`.
2. Восстанавливать context из сообщения.
3. Вызывать `ActivityHandler`.
4. После успеха вызывать `bizproc event()->send(...)`.
5. При ошибке пробрасывать исключение в Messenger.
6. Создать и зарегистрировать `app:activity-worker`.

Результат этапа:
- одно сообщение из очереди проходит полный асинхронный цикл обработки.

## 8. Добавить наблюдаемость
1. Проставить единый набор лог-полей.
2. Добавить логи publish/receive/retry/failure/success.
3. Проверить, что по логам можно проследить жизненный цикл сообщения.

Результат этапа:
- видно, что произошло с каждым activity и на каком этапе.

## 9. Написать тесты
1. Unit-тесты на mapping.
2. Unit-тесты на `ActivityHandler`.
3. Unit-тесты на `createFromWorkflowAuth()`.
4. Integration-тесты на endpoint publish.
5. Integration-тесты на worker success/retry/failure transport.

Результат этапа:
- основные риски закрыты автоматическими проверками.

## 10. Провести ручную приёмку
1. Поднять RabbitMQ.
2. Запустить 2 worker-процесса.
3. Отправить activity из Bitrix24.
4. Проверить быстрый HTTP-ответ.
5. Проверить успешную обработку worker.
6. Смоделировать ошибку и проверить retries + `activity_failed`.

Результат этапа:
- подтверждено, что схема работает end-to-end в локальной среде.
