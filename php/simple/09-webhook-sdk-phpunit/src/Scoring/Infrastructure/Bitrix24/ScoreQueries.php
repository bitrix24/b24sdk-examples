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

namespace App\Scoring\Infrastructure\Bitrix24;

use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelMapper;
use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelQueries;
use App\Scoring\RiskLevel;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactsResult;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;

readonly class ScoreQueries
{
    public function __construct(
        private RiskLevelQueries $riskLevelQueries,
        private RiskLevelMapper $riskLevelMapper,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Filter contacts without risk level
     *
     * @param array<string, mixed> $order
     * @param array<string, mixed> $filter
     * @param array<int, string> $select
     */
    public function filterContactsWithoutRiskLevel(
        ServiceBuilder $b24ServiceBuilder,
        array $order = [],
        array $filter = [],
        array $select = [],
        int $start = 0,
    ): ContactsResult {
        $this->logger->debug('ScoreQueries.filterContactsWithoutRiskLevel', [
            'filter' => $filter,
        ]);
        return $b24ServiceBuilder->getCRMScope()->contact()->list(
            $order,
            array_merge(
                $filter,
                [
                    // filter contacts without risk level as smart process item
                    $this->riskLevelMapper->getSmartProcessFieldName($b24ServiceBuilder) => '',
                ]
            ),
            $select,
            $start,
        );
    }

    /**
     * @param array<string, mixed> $filter
     * @param array<string, mixed> $order
     * @param array<int, string> $select
     */
    public function filterContactsWithRiskLevel(
        ServiceBuilder $b24ServiceBuilder,
        RiskLevel $riskLevel,
        array $filter = [],
        array $order = [],
        array $select = [],
        int $start = 0,
    ): ContactsResult {
        $this->logger->debug('ScoreQueries.filterContactsWithRiskLevel', [
            'riskLevel' => $riskLevel->value,
            'filter' => $filter,
        ]);

        $indexedLevel = $this->riskLevelQueries->getRiskLevels($b24ServiceBuilder, $this->riskLevelMapper->getEntityTypeId($b24ServiceBuilder));

        return $b24ServiceBuilder->getCRMScope()->contact()->list(
            $order,
            array_merge(
                $filter,
                [
                    // filter contacts without risk level as smart process item
                    $this->riskLevelMapper->getSmartProcessFieldName($b24ServiceBuilder) => $indexedLevel[$riskLevel->value]->b24EntityId,
                ]
            ),
            $select,
            $start,
        );
    }
}
