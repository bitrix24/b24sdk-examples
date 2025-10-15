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

readonly class RiskLevelInstaller
{
    private const string RISK_SP_CODE = 'RISK_LEVELS';

    public function __construct(
        private RiskLevelCommands $riskLevelCommands,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function install(ServiceBuilder $b24ServiceBuilder): void
    {
        $this->logger->debug('RiskLevelInstaller.install.start', [
            'b24DomainUrl' => $b24ServiceBuilder->core->getApiClient()->getCredentials()->getDomainUrl()
        ]);

        // create smart process if not exists
        $searchResult = $b24ServiceBuilder->getCRMScope()->type()->list([], ['code' => self::RISK_SP_CODE])->getTypes();
        if ($searchResult === []) {
            $addResult = $b24ServiceBuilder->getCRMScope()->type()->add(
                'Risk Levels',
                null,
                [
                    'code' => self::RISK_SP_CODE,
                    'relations' => [
                        'child' => [
                            [
                                // allow bind to contact
                                'entityTypeId' => 3,
                                'isChildrenListEnabled' => 'N',
                                'isPredefined' => 'N'
                            ]
                        ]
                    ]
                ]
            );
            $this->logger->debug('RiskLevelInstaller.install.smartProcessCreated', [
                'id' => $addResult->getId(),
                'entityTypeId' => $addResult->type()->entityTypeId
            ]);
            $entityTypeId = $addResult->type()->entityTypeId;
        } else {
            $this->logger->debug('RiskLevelInstaller.install.smartProcessExists', [
                'id' => $searchResult[0]->id,
                'entityTypeId' => $searchResult[0]->entityTypeId
            ]);
            $entityTypeId = $searchResult[0]->entityTypeId;
        }
        $this->logger->debug('RiskLevelInstaller.riskLevels.SmartProcess', ['id' => $entityTypeId]);

        // fill risk levels
        foreach (RiskLevel::cases() as $level) {
            // create risk level, if level exists - return current id
            $this->riskLevelCommands->addLevel($b24ServiceBuilder, $entityTypeId, $level);
        }
    }
}
