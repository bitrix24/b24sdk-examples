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
use App\Scoring\Infrastructure\Bitrix24\RiskLevels\SmartProcessItemRiskLevel;
use App\Scoring\Score;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ScoreCommands
{
    public function __construct(
        private RiskLevelMapper $riskLevelMapper,
        private ScoreFieldMapper $scoreFieldMapper,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param ServiceBuilder $b24ServiceBuilder
     * @param SmartProcessItemRiskLevel[] $indexedRiskLevels
     * @param int $b24ContactId
     * @param Score $score
     * @return void
     * @throws BaseException
     * @throws TransportException
     */
    public function setScore(
        ServiceBuilder $b24ServiceBuilder,
        array $indexedRiskLevels,
        int $b24ContactId,
        Score $score
    ): void {
        $this->logger->debug('setScore.start', [
            'b24ContactId' => $b24ContactId,
            'risk' => $score->risk->value,
            'scores' => $score->scores,
        ]);

        if (!$b24ServiceBuilder->getCRMScope()->contact()->update(
            $b24ContactId,
            [
                // set score
                'UF_CRM_' . $this->scoreFieldMapper->getName() => $score->scores,
                // bind smart process item (risk level) to contact
                'PARENT_ID_' . $this->riskLevelMapper->getEntityTypeId($b24ServiceBuilder) => $indexedRiskLevels[$score->risk->value]->b24EntityId,
            ],
        )->isSuccess()) {
            throw new RuntimeException('Failed to set score');
        }

        $this->logger->debug('setScore.finish');
    }
}
