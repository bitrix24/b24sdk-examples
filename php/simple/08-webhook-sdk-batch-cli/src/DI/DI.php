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

namespace App\DI;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DI
{
    private static self $instance;

    private readonly ContainerBuilder $container;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        $container = new ContainerBuilder();
        $fileLocator = new FileLocator(dirname(__DIR__, 2) . '/config');
        $yamlFileLoader = new YamlFileLoader($container, $fileLocator);
        $yamlFileLoader->load('services.yaml');

        $container->compile();
        $this->container = $container;
    }

    /**
     * @param non-empty-string $id
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public static function get(string $id): mixed
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance->container->get($id);
    }
}
