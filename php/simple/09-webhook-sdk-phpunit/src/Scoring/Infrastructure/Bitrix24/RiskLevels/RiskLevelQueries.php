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

use App\Scoring\RiskLevel;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactsResult;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;

readonly class RiskLevelQueries
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Get Risk Levels mapped on Smart Process Items
     *
     * @return SmartProcessItemRiskLevel[]
     */
    public function getRiskLevels(ServiceBuilder $b24ServiceBuilder, int $entityTypeId): array
    {
        $this->logger->debug('RiskLevelQueries.getRiskLevels.start', [
            'entityTypeId' => $entityTypeId,
        ]);

        $items = $b24ServiceBuilder->getCRMScope()->item()->list(
            $entityTypeId,
            [],
            [],
// todo add issue
//            ['id', 'entityTypeId', 'xmlId','title']
            ['*']
        )->getItems();
        $result = [];
        foreach ($items as $item) {
            $result[] = new SmartProcessItemRiskLevel(
                RiskLevel::from($item->xmlId),
                $item->id,
                $item->entityTypeId
            );
        }
        // index result by risk level
        return array_reduce(
            $result,
            static function (array $acc, SmartProcessItemRiskLevel $item) {
                $acc[$item->risk->value] = $item;
                return $acc;
            },
            []
        );
    }
}
