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

readonly class ScoreInstaller
{
    public function __construct(
        private ScoreFieldMapper $scoreFieldMapper,
        private LoggerInterface $logger
    ) {
    }

    public function install(ServiceBuilder $b24ServiceBuilder): void
    {
        $this->logger->debug('ScoreInstaller.install.start', [
            'b24DomainUrl' => $b24ServiceBuilder->core->getApiClient()->getCredentials()->getDomainUrl()
        ]);

        // check, are fields exists
        $contactFields = $b24ServiceBuilder->getCRMScope()->contactUserfield()->list(
            [],
            [],
        )->getUserfields();
        $fieldsNames = array_column($contactFields, 'XML_ID');
        // add score field
        if (!in_array($this->scoreFieldMapper->getXmlId(), $fieldsNames, true)) {
            $b24FieldId = $b24ServiceBuilder->getCRMScope()->contactUserfield()->add(
                [
                    'USER_TYPE_ID' => 'integer',
                    'FIELD_NAME' => $this->scoreFieldMapper->getName(),
                    'LABEL' => 'App: User Score',
                    'SHOW_FILTER' => 'Y',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'N',
                    'SETTINGS' => [
                        'DEFAULT_VALUE' => 0
                    ],
                    'XML_ID' => $this->scoreFieldMapper->getXmlId(),
                ]
            )->getId();
            $this->logger->debug('ScoreFieldsMapper.installFields.fieldInstalled', [
                'name' => $this->scoreFieldMapper->getName(),
                'b24FieldId' => $b24FieldId,
            ]);
        } else {
            $this->logger->debug('ScoreFieldsMapper.installFields.fieldExists');
        }
    }
}
