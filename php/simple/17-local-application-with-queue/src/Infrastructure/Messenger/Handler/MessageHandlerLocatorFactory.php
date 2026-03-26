<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Handler;

use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;

final readonly class MessageHandlerLocatorFactory
{
    public static function create(iterable $handlers): HandlersLocator
    {
        $mapping = [];

        foreach ($handlers as $handler) {
            $reflectionMethod = new ReflectionMethod($handler, '__invoke');
            $parameters = $reflectionMethod->getParameters();

            if ($parameters === []) {
                throw new \LogicException(sprintf('Message handler "%s" must declare the message argument in __invoke().', $handler::class));
            }

            $messageType = $parameters[0]->getType();
            if (!$messageType instanceof ReflectionNamedType || $messageType->isBuiltin()) {
                throw new \LogicException(sprintf('Message handler "%s" must type-hint a message class in __invoke().', $handler::class));
            }

            $mapping[$messageType->getName()][] = new HandlerDescriptor([$handler, '__invoke']);
        }

        return new HandlersLocator($mapping);
    }
}
