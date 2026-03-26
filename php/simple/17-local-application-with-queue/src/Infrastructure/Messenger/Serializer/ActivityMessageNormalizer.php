<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Serializer;

use App\Message\ActivityMessage;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActivityMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof ActivityMessage) {
            throw new InvalidArgumentException(sprintf('Unsupported message type "%s".', get_debug_type($data)));
        }

        return $data->toArray();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ActivityMessage;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ActivityMessage
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException(sprintf('ActivityMessage payload must be an array, got "%s".', get_debug_type($data)));
        }

        return ActivityMessage::fromArray($data);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === ActivityMessage::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ActivityMessage::class => true,
        ];
    }
}
