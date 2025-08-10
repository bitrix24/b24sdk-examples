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


use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;

class ScoreFieldsMapper
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function installFields(ServiceBuilder $b24ServiceBuilder): void
    {
        $this->logger->debug('ScoreFieldsMapper.installFields.start', [
            'b24DomainUrl' => $b24ServiceBuilder->core->getApiClient()->getCredentials()->getDomainUrl()
        ]);

        // check, is fields exists
        $contactFields = $b24ServiceBuilder->getCRMScope()->contactUserfield()->list(
            [],
            [],
        )->getUserfields();

        dump($contactFields);


    }

    public function getRiskFieldName(): string
    {
        return 'RISK';
    }

    public function getScoresFieldName(): string
    {
        return 'SCORES';
    }
}
