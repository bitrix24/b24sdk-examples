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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

readonly class RiskLevelMapper
{
    private const string RISK_SP_CODE = 'RISK_LEVELS';

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return positive-int
     * @throws TransportException
     * @throws BaseException
     * @throws Exception
     */
    public function getEntityTypeId(ServiceBuilder $b24ServiceBuilder): int
    {
        $this->logger->debug('RiskLevelMapper.getEntityTypeId.start', [
            'b24DomainUrl' => $b24ServiceBuilder->core->getApiClient()->getCredentials()->getDomainUrl()
        ]);

        //todo add in memory cache here
        $searchResult = $b24ServiceBuilder->getCRMScope()->type()->list([], ['code' => self::RISK_SP_CODE])->getTypes();
        if ($searchResult !== []) {
            $this->logger->debug('RiskLevelInstaller.install.smartProcessExists', [
                'id' => $searchResult[0]->id,
                'entityTypeId' => $searchResult[0]->entityTypeId
            ]);

            $entityTypeId = $searchResult[0]->entityTypeId;
            if ($entityTypeId <= 0) {
                throw new RuntimeException('Invalid entity type ID');
            }

            return $entityTypeId;
        }

        throw new RuntimeException('Smart process not found');
    }

    public function getSmartProcessFieldName(ServiceBuilder $b24ServiceBuilder): string
    {
        return 'PARENT_ID_' . $this->getEntityTypeId($b24ServiceBuilder);
    }
}
