<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Infrastructure\Messenger\Container\ArrayServiceLocator;
use App\Infrastructure\Messenger\Retry\FixedDelayRetryStrategy;
use App\Infrastructure\Messenger\Serializer\ActivityMessageNormalizer;
use App\Infrastructure\Messenger\Serializer\ActivityMessageTransportSerializer;
use App\Message\ActivityMessage;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory;
use Symfony\Component\Messenger\EventListener\DispatchPcntlSignalListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\FailedMessageProcessingMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface as MessengerSerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

final readonly class MessengerFactory
{
    private const string EXCHANGE_NAME = 'activity';
    private const string ACTIVITY_QUEUE_NAME = 'activity_queue';
    private const string ACTIVITY_ROUTING_KEY = 'activity';
    private const string FAILURE_QUEUE_NAME = 'activity_failed';
    private const string FAILURE_ROUTING_KEY = 'activity_failed';

    public static function createSymfonySerializer(): SymfonySerializerInterface
    {
        return new Serializer(
            [
                new ActivityMessageNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
    }

    public static function createTransportSerializer(SymfonySerializerInterface $serializer): MessengerSerializerInterface
    {
        return new ActivityMessageTransportSerializer($serializer);
    }

    public static function createActivityTransport(MessengerSerializerInterface $serializer): TransportInterface
    {
        return self::createTransport(
            $serializer,
            self::ACTIVITY_QUEUE_NAME,
            self::ACTIVITY_ROUTING_KEY,
        );
    }

    public static function createFailureTransport(MessengerSerializerInterface $serializer): TransportInterface
    {
        return self::createTransport(
            $serializer,
            self::FAILURE_QUEUE_NAME,
            self::FAILURE_ROUTING_KEY,
        );
    }

    public static function createTransportLocator(
        TransportInterface $activityTransport,
        TransportInterface $failureTransport,
    ): ContainerInterface {
        return new ArrayServiceLocator([
            MessengerTransportNames::ACTIVITY => $activityTransport,
            MessengerTransportNames::ACTIVITY_FAILED => $failureTransport,
        ]);
    }

    public static function createSendersLocator(ContainerInterface $transportLocator): SendersLocator
    {
        return new SendersLocator(
            [
                ActivityMessage::class => [MessengerTransportNames::ACTIVITY],
            ],
            $transportLocator,
        );
    }

    public static function createFailureSenderLocator(TransportInterface $failureTransport): ContainerInterface
    {
        return new ArrayServiceLocator([
            MessengerTransportNames::ACTIVITY => $failureTransport,
        ]);
    }

    public static function createRetryStrategyLocator(): ContainerInterface
    {
        return new ArrayServiceLocator([
            MessengerTransportNames::ACTIVITY => new FixedDelayRetryStrategy([1000, 5000, 10000]),
        ]);
    }

    public static function createEventDispatcher(
        ContainerInterface $transportLocator,
        ContainerInterface $retryStrategyLocator,
        ContainerInterface $failureSenderLocator,
        LoggerInterface $logger,
    ): EventDispatcherInterface {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new DispatchPcntlSignalListener());
        $eventDispatcher->addSubscriber(
            new SendFailedMessageForRetryListener(
                $transportLocator,
                $retryStrategyLocator,
                $logger,
                $eventDispatcher,
            )
        );
        $eventDispatcher->addSubscriber(
            new SendFailedMessageToFailureTransportListener(
                $failureSenderLocator,
                $logger,
                [
                    MessengerTransportNames::ACTIVITY => MessengerTransportNames::ACTIVITY_FAILED,
                ],
            )
        );

        return $eventDispatcher;
    }

    public static function createMessageBus(
        SendersLocator $sendersLocator,
        HandlersLocator $handlersLocator,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
    ): MessageBusInterface {
        $sendMessageMiddleware = new SendMessageMiddleware($sendersLocator, $eventDispatcher, false);
        $sendMessageMiddleware->setLogger($logger);

        $handleMessageMiddleware = new HandleMessageMiddleware($handlersLocator);
        $handleMessageMiddleware->setLogger($logger);

        return new MessageBus([
            new FailedMessageProcessingMiddleware(),
            $sendMessageMiddleware,
            $handleMessageMiddleware,
        ]);
    }

    public static function createWorker(
        TransportInterface $activityTransport,
        MessageBusInterface $messageBus,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
    ): Worker {
        return new Worker(
            [
                MessengerTransportNames::ACTIVITY => $activityTransport,
            ],
            $messageBus,
            $eventDispatcher,
            $logger,
        );
    }

    private static function createTransport(
        MessengerSerializerInterface $serializer,
        string $queueName,
        string $routingKey,
    ): TransportInterface {
        $transportFactory = new AmqpTransportFactory();

        return $transportFactory->createTransport(
            'amqp://',
            [
                'host' => self::getRequiredEnv('RABBITMQ_HOST'),
                'port' => (int) self::getRequiredEnv('RABBITMQ_PORT'),
                'vhost' => self::getRequiredEnv('RABBITMQ_VHOST'),
                'login' => self::getRequiredEnv('RABBITMQ_USER'),
                'password' => self::getRequiredEnv('RABBITMQ_PASS'),
                'exchange' => [
                    'name' => self::EXCHANGE_NAME,
                    'type' => 'direct',
                    'default_publish_routing_key' => $routingKey,
                ],
                'queues' => [
                    $queueName => [
                        'binding_keys' => [$routingKey],
                    ],
                ],
                'auto_setup' => true,
            ],
            $serializer,
        );
    }

    private static function getRequiredEnv(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if (!is_string($value) || $value === '') {
            throw new \RuntimeException(sprintf('Environment variable "%s" is required for Messenger transport configuration.', $key));
        }

        return $value;
    }
}
