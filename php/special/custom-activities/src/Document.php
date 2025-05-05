<?php 

declare(strict_types=1);

namespace App;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Application\Local\Infrastructure\Filesystem\AppAuthFileStorage;
use Bitrix24\SDK\Application\Local\Repository\LocalAppAuthRepositoryInterface;
use Bitrix24\SDK\Application\Requests\Placement\PlacementRequest;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Bitrix24\SDK\Events\AuthTokenRenewedEvent;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Bitrix24\SDK\Services\Placement\Service\PlacementLocationCode;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Bitrix24\SDK\Core\Credentials\AuthToken;


class Document {
    
    private ServiceBuilder $b24;
    private LoggerInterface $logger;

    public function __construct(ServiceBuilder $serviceBuilder, LoggerInterface $logger) {
        $this->b24 = $serviceBuilder;
        $this->logger = $logger;
    }

    // register a document in Document Management System, check email addresses, etc
    public function registerDocument (int $documentId, string $employeeEmail, string $contactEmail) {
        $this->logger->debug('document.init', [
            'documentId' => $documentId,
            'employeeEmail' => $employeeEmail,
            'contactEmail' => $contactEmail
        ]);

        // pretend to register a document successfully
        return 123; // id of the registered document
    }

    // status = 0 - not signed, status = 1 - signed by user, status = 2 - signed by employee, 3 - signed by both
    public function prepareConfigurableActivity (int $status, string $employeeEmail, string $contactEmail, int $externalDocumentId): array {

        $result = [];

        if (in_array($status, [0, 2])) {
            $buttons = [
                'title' => 'Sign',
                'action' => [
                    'type' => 'redirect',
                    'id' => 'sign',
                    'uri' => 'https://esign-system.com/sign/' . $externalDocumentId,
                ],
                'type' => 'primary',
            ];
        }

        $statusTexts = [
            0 => 'unsigned',
            1 => 'signed by client',
            2 => 'signed by employee',
            3 => 'signed',
        ];

        $statusTypes = [
            0 => 'warning',
            1 => 'warning',
            2 => 'warning',
            3 => 'success'
        ];

        $result = [
            'icon' => [
                'code' => 'document'
            ],
            'header' => [
                'title' => 'Sign document',
                'tags' => [
                    'status' => ['title' => $statusTexts[$status], 'type' => $statusTypes[$status]],
                ]
            ],
            'body' => [
                'logo' => [
                    'code' => 'document',
                ],
                'blocks' => [
                    'docName' => [
                        'type' => 'withTitle',
                        'properties' => [
                            'title' => 'document',
                            'inline' => false,
                            'block' => [
                                'type' => 'text',
                                'properties' => [
                                    'value' => 'Here is the document name',
                                    'multiline' => true,
                                ],
                            ]
                        ]
                    ],
                    'contact' => [
                        'type' => 'withTitle',
                        'properties' => [
                            'title' => 'Client email',
                            'inline' => false,
                            'block' => [
                                'type' => 'text',
                                'properties' => [
                                    'value' => $contactEmail,
                                    'multiline' => true,
                                ],
                            ]
                        ]
                    ],
                    'employee' => [
                        'type' => 'withTitle',
                        'properties' => [
                            'title' => 'Employee email',
                            'inline' => false,
                            'block' => [
                                'type' => 'text',
                                'properties' => [
                                    'value' => $employeeEmail,
                                    'multiline' => true,
                                ],
                            ]
                        ]
                    ],
                ]
            ],
            'footer' => [
                'buttons' => [
                    'button1' => $buttons,
                ]
            ],
        ];

        return $result;
    }

    public function createActivity (int $documentId, string $employeeEmail, string $contactEmail, int $externalDocumentId, int $dealId) {
        
        $this->logger->debug('document.init', [
            'documentId' => $documentId,
            'employeeEmail' => $employeeEmail,
            'contactEmail' => $contactEmail,
            'externalDocumentId' => $externalDocumentId,
            'dealId' => $dealId
        ]);

        $result = $this->b24->getMainScope()->core->call(
            'crm.activity.configurable.add',
            [
                'ownerTypeId' => 2, // deal
                'ownerId' => $dealId,
                'fields' => [
                    'responsibleId' => 1, // actually we need to get the responsibleId from the deal
                    'isIncomingChannel' => 'N',
                    'originatorId' => 'esignService',
                    'originId' => $externalDocumentId, // id of a document in the external system (guid or something else).
                    'completed' => 'N',
                ],
                'layout' => $this->prepareConfigurableActivity(0, $employeeEmail, $contactEmail, $externalDocumentId)
            ]
        );
        return $result;
    }

    public function findDocumentActivity (int $externalDocumentId) {
        $result = $this->b24->getMainScope()->core->call(
            'crm.activity.list',
            [
                'filter' => [
                    'originId' => $externalDocumentId,
                    'originatorId' => 'esignService',
                ],
                'select' => ['ID', 'OWNER_ID', 'OWNER_TYPE_ID', 'ORIGIN_ID', 'ORIGINATOR_ID', 'COMPLETED'],
            ]
        );
        return $result;
    }

    public function changeDocumentStatus (int $externalDocumentId, int $newStatus) {
        $result = $this->findDocumentActivity($externalDocumentId);

        if (count($result->getResponseData()->getResult()) > 0) {
            $activityId = $result->getResponseData()->getResult()[0]['ID'];

            // Make the updated structure with the new status

            // $layout = $this->prepareConfigurableActivity($newStatus, ...);

            $completed = $newStatus == 3 ? 'Y' : 'N';
            

            // update activity

            /*
            $result = $this->b24->getMainScope()->core->call(
                'crm.activity.configurable.update',
                [
                    'id' => $activityId, // deal
                    'fields' => [
                        'completed' => $completed,
                    ],
                    'layout' => $this->prepareConfigurableActivity(0, $employeeEmail, $contactEmail, $externalDocumentId)
                ]
            );
            return $result;
            */

        } else return false;
    }
}