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
         * @var non-empty-string
         */
        public string $robotCode,
        /**
         * @var non-empty-string
         */
        public string $handlerUrl,
        /**
         * @var int
         */
        public int $b24AuthUserId,
        /**
         * @var bool
         */
        public bool $isUseSubscription,
        /**
         * @var array<non-empty-string, non-empty-string>
         */
        public array $name,
        /**
         * @var array<non-empty-string, non-empty-string>
         */
        public array $description,
        /**
         * @var array
         */
        public array $properties,
        /**
         * @var array
         */
        public array $returnProperties,
        /**
         * ['crm', 'CCrmDocumentLead', 'LEAD'] — лиды
         * ['crm', 'CCrmDocumentDeal', 'DEAL'] — сделки
         * ['crm', 'Bitrix\Crm\Integration\BizProc\Document\Quote', 'QUOTE']
         * ['crm', 'Bitrix\Crm\Integration\BizProc\Document\SmartInvoice', 'SMART_INVOICE']
         * ['crm', 'Bitrix\Crm\Integration\BizProc\Document\Dynamic', 'DYNAMIC_XXX'] XXX - smart process id
         *
         * @var WorkflowDocumentType
         */
        public WorkflowDocumentType $documentType,
        /**
         * @var array
         */
        public array $filter = [],
        /**
         * @var bool
         */
        public bool $isUsePlacement = false
    ) {
    }
}

