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


namespace App\Scoring\Infrastructure\Bitrix24\RiskLevels;

use App\Scoring\DTO\RiskLevel;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;

readonly class RiskLevelCommands
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Add risk level to CRM
     *
     * risk level stored in CRM in field "XML_ID"
     *
     * @throws TransportException
     * @throws BaseException
     */
    public function addLevel(ServiceBuilder $b24ServiceBuilder, int $entityTypeId, RiskLevel $riskLevel): int
    {
        $this->logger->debug('RiskLevelCommands.addLevel.start', [
            'entityTypeId' => $entityTypeId,
            'riskLevel' => $riskLevel,
        ]);

        $searchResult = $b24ServiceBuilder->getCRMScope()->item()->list(
            $entityTypeId,
            [],
            [
                'xmlId' => $riskLevel->value
            ],
            ['*']
        );

        if ($searchResult->getCoreResponse()->getResponseData()->getPagination()->getTotal() === 0) {
            $addResult = $b24ServiceBuilder->getCRMScope()->item()->add($entityTypeId, [
                'title' => $riskLevel->name,
                'xmlId' => $riskLevel->value,
            ])->item();
            $this->logger->debug('RiskLevelCommands.addLevel.created', [
                'id' => $addResult->id,
                'level' => $riskLevel->value,
            ]);
            $levelId = $addResult->id;
        } else {
            $this->logger->debug('RiskLevelCommands.addLevel.AlreadyExists', [
                'id' => $searchResult->getItems()[0]->id,
            ]);
            $levelId = $searchResult->getItems()[0]->id;
        }
        return $levelId;
    }
}
