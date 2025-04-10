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
                'title' => 'Подписать',
                'action' => [
                    'type' => 'redirect',
                    'id' => 'sign',
                    'uri' => 'https://documentolog.com/sign/' . $externalDocumentId,
                ],
                'type' => 'primary',
            ];
        }

        $statusTexts = [
            0 => 'не подписан',
            1 => 'подписан клиентом',
            2 => 'подписан сотрудником',
            3 => 'подписан'
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
                'title' => 'Подписать документ',
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
                            'title' => 'документ',
                            'inline' => false,
                            'block' => [
                                'type' => 'text',
                                'properties' => [
                                    'value' => 'Сюда втыкаем название документа',
                                    'multiline' => true,
                                ],
                            ]
                        ]
                    ],
                    'contact' => [
                        'type' => 'withTitle',
                        'properties' => [
                            'title' => 'e-mail клиента',
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
                            'title' => 'e-mail сотрудника',
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

        $buttons = [
            'title' => 'Подписать',
            'action' => [
                'type' => 'redirect',
                'id' => 'sign',
                'uri' => 'https://ya.ru'
            ],
            'type' => 'primary',
        ];

        $result = $this->b24->getMainScope()->core->call(
            'crm.activity.configurable.add',
            [
                'ownerTypeId' => 2, // deal
                'ownerId' => $dealId,
                'fields' => [
                    'responsibleId' => 1, // вообще надо получать текущего пользователя
                    'isIncomingChannel' => 'N',
                    'originatorId' => 'documentolog',
                    'originId' => $externalDocumentId, // id в источнике данных (guid инвойса).
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
                    'originatorId' => 'documentolog',
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

            // Формируем обновленную структуру с новым статусом

            // $layout = $this->prepareConfigurableActivity($newStatus, ...);

            $completed = $newStatus == 3 ? 'Y' : 'N';
            
            // обновляем дело в таймлайне
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