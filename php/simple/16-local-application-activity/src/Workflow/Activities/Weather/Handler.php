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

namespace App\Workflow\Activities\Weather;

use App\Workflow\Activities\ActivityHandlerInterface;
use App\Workflow\Activities\ActivityHandlerMetadata;
use App\Workflow\Activities\ActivityInstallMetadata;
use App\Workflow\Activities\ActivityRequest;
use App\Workflow\Activities\ActivityResponse;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Psr\Log\LoggerInterface;

final readonly class Handler implements ActivityHandlerInterface
{
    private const string CODE = 'weather_activity';

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * todo add to interface in sdk
     * @param ActivityRequest $activityRequest
     * @return ActivityResponse
     */
    public function handle(ActivityRequest $activityRequest): ActivityResponse
    {
        $this->logger->debug('Weather.ActivityHandler.start', [
            'code' => $activityRequest->code,
            'properties' => $activityRequest->properties,
        ]);
        // сложная бизнес-логика

        // 1 - в сложной бизнес-логке получаем настройки сразу
        // $activityRequest->properties['city']
        // $activityRequest->properties['country']

        // 2- настройки прокидываем в отдельные переменные + проверки

//      todo add check for required properties
//      $country = $activityRequest->properties['country'];
//      $city = $activityRequest->properties['city'];

        // 2 - инстанцируем объект настроек
//        $prop = new Properties(
//            $activityRequest->properties['country'],
//            $activityRequest->properties['city']
//        );

        // 3 - используем фабричный метод для создания объекта настроек
        $prop = Properties::initFromArray($activityRequest->properties);
        var_dump($prop);

        // 4 - используем компонент symfony serializer
        $prop = $this->serializer->denormalize($activityRequest->properties, Properties::class);

        // complex business logic
        //sleep(20);



        $this->logger->debug('Weather.ActivityHandler.finish');
        return new ActivityResponse(
            $activityRequest->eventToken,
            (new Result(50, 'temperature is 50 degrees'))->toArray(),
            'log message from robot'
        );
    }

    /**
     * todo add to interface in sdk
     * @return ActivityHandlerMetadata
     */
    public function getHandlerMetadata(): ActivityHandlerMetadata
    {
        return new ActivityHandlerMetadata(self::CODE);
    }

    /**
     * todo add to interface in sdk
     * @param string|null $handlerUrl
     * @param int|null $b24UserId
     * @return ActivityInstallMetadata
     */
    public function getInstallMetadata(?string $handlerUrl, ?int $b24UserId): ActivityInstallMetadata
    {
        return new ActivityInstallMetadata(
            self::CODE,
            $handlerUrl,
            $b24UserId,
            true,
            [
                'ru' => 'Активити для прогноза погоды'
            ],
            [
                'ru' => 'Активити для прогноза погоды на текущий день'
            ],
            Properties::getMetadata(),
            Result::getMetadata(),
            WorkflowDocumentType::buildForDeal(),
            [],
            false
        );
    }
}