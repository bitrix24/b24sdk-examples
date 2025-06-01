<?php

/**
 * This file is part of the b24sdk examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Robots;

use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;

final readonly class RobotInstallMetadata
{
    public function __construct(
        /**
         * Внутренний идентификатор робота. Является уникальным в рамках приложения.
         * Допустимые символы — a-z, A-Z, 0-9, точка, дефис и нижнее подчеркивание _
         * @var non-empty-string
         */
        public string $robotCode,
        /**
         * URL, на который робот будет отправлять данные через сервер очередей bitrix24.
         * В ссылке должен быть тот же домен, на котором установлено приложение
         * @var non-empty-string
         */
        public string $handlerUrl,
        /**
         * Идентификатор пользователя, токен которого будет передан приложению
         * @var int
         */
        public int $b24AuthUserId,
        /**
         * Должен ли робот ожидать ответа от приложения. Возможные значения:
         * @var bool
         */
        public bool $isUseSubscription,
        /**
         * Название робота.
         *
         * Может быть строкой или ассоциативным массивом локализированных строк вида:
         * @var array<non-empty-string, non-empty-string>
         */
        public array $name,
        /**
         * Описание робота.
         *
         * Может быть строкой или ассоциативным массивом локализированных строк вида:
         * @var array<non-empty-string, non-empty-string>
         */
        public array $description,
        /**
         * Объект с параметрами робота. Содержит объекты, каждый из которых описывает параметр робота.
         * Системное название параметра должно начинаться с буквы и может содержать символы a-z, A-Z, 0-9 и нижнее подчеркивание _
         * @var array
         */
        public array $properties,
        /**
         * Объект с дополнительными результатами робота. Содержит объекты, каждый из которых описывает параметр робота.
         * Параметр управляет возможностью робота ожидать ответа приложения и работать с данными, которые придут в ответе.
         * Системное название параметра должно начинаться с буквы и может содержать символы a-z, A-Z, 0-9 и нижнее подчеркивание _
         * @var array
         */
        public array $returnProperties,
        /**
         * Тип документа, который будет определять типы данных для параметров PROPERTIES и RETURN_PROPERTIES.
         * Состоит из трех элементов типа строка:
         * - идентификатор модуля
         * - идентификатор объекта
         * - тип документа
         *
         * Возможные варианты значений:
         *
         * Модуль CRM
         * ['crm', 'CCrmDocumentLead', 'LEAD'] — лиды
         * ['crm', 'CCrmDocumentDeal', 'DEAL'] — сделки
         * ['crm', 'Bitrix\Crm\Integration\BizProc\Document\Quote', 'QUOTE'] — коммерческие предложения
         * ['crm', 'Bitrix\Crm\Integration\BizProc\Document\SmartInvoice', 'SMART_INVOICE'] — счета
         * ['crm', 'Bitrix\Crm\Integration\BizProc\Document\Dynamic', 'DYNAMIC_XXX'] — смарт-процессы, где XXX — идентификатор смарт-процесса
         *
         * @var WorkflowDocumentType
         */
        public WorkflowDocumentType $documentType,
        /**
         * Объект с правилами ограничения робота по типу документа и редакции.
         *
         * Может содержать ключи:
         *
         * INCLUDE — массив правил, где робот будет отображен
         * EXCLUDE — массив правил, где робот будет скрыт
         * Каждое правило в массиве может быть строкой или массивом типа документа в полном или частичном варианте.
         * @var array
         */
        public array $filter = [],
        /**
         * Дает возможность открывать дополнительные настройки робота в слайдере приложения.
         * @var bool
         */
        public bool $isUsePlacement = false
    ) {
    }
}

