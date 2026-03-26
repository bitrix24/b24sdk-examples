<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Container;

use Psr\Container\ContainerInterface;

final readonly class ArrayServiceLocator implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(
        private array $services,
    ) {
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException(sprintf('Service "%s" is not registered in the locator.', $id));
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}
