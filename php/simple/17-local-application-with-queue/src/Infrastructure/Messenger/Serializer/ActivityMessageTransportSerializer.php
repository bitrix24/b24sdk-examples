<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Serializer;

use App\Message\ActivityMessage;
use DateTimeImmutable;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

final readonly class ActivityMessageTransportSerializer implements SerializerInterface
{
    private const string STAMPS_HEADER = 'X-Activity-Message-Stamps';

    public function __construct(
        private SymfonySerializerInterface $serializer,
    ) {
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        if (!isset($encodedEnvelope['body'], $encodedEnvelope['headers']['type'])) {
            throw new MessageDecodingFailedException('Encoded envelope must contain body and headers[type].');
        }

        $type = (string) $encodedEnvelope['headers']['type'];
        if ($type !== ActivityMessage::class) {
            throw new MessageDecodingFailedException(sprintf('Unsupported message type "%s".', $type));
        }

        $message = $this->serializer->deserialize((string) $encodedEnvelope['body'], $type, 'json');
        if (!$message instanceof ActivityMessage) {
            throw new MessageDecodingFailedException(sprintf('Decoded message must be "%s".', ActivityMessage::class));
        }

        return new Envelope($message, $this->decodeStamps($encodedEnvelope['headers']));
    }

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();
        if (!$message instanceof ActivityMessage) {
            throw new \LogicException(sprintf('Only "%s" can be encoded by this transport serializer, got "%s".', ActivityMessage::class, $message::class));
        }

        return [
            'body' => $this->serializer->serialize($message, 'json'),
            'headers' => array_filter([
                'type' => ActivityMessage::class,
                'Content-Type' => 'application/json',
                self::STAMPS_HEADER => $this->encodeStamps($envelope),
            ]),
        ];
    }

    /**
     * @return array<int, object>
     */
    private function decodeStamps(array $headers): array
    {
        if (!isset($headers[self::STAMPS_HEADER])) {
            return [];
        }

        try {
            /** @var array<string, array<string, scalar|null>> $payload */
            $payload = json_decode((string) $headers[self::STAMPS_HEADER], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new MessageDecodingFailedException('Unable to decode message stamps.', 0, $exception);
        }

        $stamps = [];

        if (isset($payload['delay']['delay'])) {
            $stamps[] = new DelayStamp((int) $payload['delay']['delay']);
        }

        if (isset($payload['redelivery']['retryCount'])) {
            $redeliveredAt = null;
            if (isset($payload['redelivery']['redeliveredAt'])) {
                $redeliveredAt = new DateTimeImmutable((string) $payload['redelivery']['redeliveredAt']);
            }

            $stamps[] = new RedeliveryStamp((int) $payload['redelivery']['retryCount'], $redeliveredAt);
        }

        if (isset($payload['failure']['originalReceiverName'])) {
            $stamps[] = new SentToFailureTransportStamp((string) $payload['failure']['originalReceiverName']);
        }

        return $stamps;
    }

    private function encodeStamps(Envelope $envelope): ?string
    {
        $envelope = $envelope->withoutStampsOfType(NonSendableStampInterface::class);
        $payload = [];

        $delayStamp = $envelope->last(DelayStamp::class);
        if ($delayStamp instanceof DelayStamp) {
            $payload['delay'] = [
                'delay' => $delayStamp->getDelay(),
            ];
        }

        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);
        if ($redeliveryStamp instanceof RedeliveryStamp) {
            $payload['redelivery'] = [
                'retryCount' => $redeliveryStamp->getRetryCount(),
                'redeliveredAt' => $redeliveryStamp->getRedeliveredAt()->format(DATE_ATOM),
            ];
        }

        $failureStamp = $envelope->last(SentToFailureTransportStamp::class);
        if ($failureStamp instanceof SentToFailureTransportStamp) {
            $payload['failure'] = [
                'originalReceiverName' => $failureStamp->getOriginalReceiverName(),
            ];
        }

        if ($payload === []) {
            return null;
        }

        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \LogicException('Unable to encode message stamps.', 0, $exception);
        }
    }
}
