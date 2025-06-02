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
    private ContainerBuilder $container;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        $container = new ContainerBuilder();
        $fileLocator = new FileLocator(dirname(__DIR__ , 2). '/config');
        $loader = new YamlFileLoader($container, $fileLocator);
        $loader->load('services.yaml');
        $container->compile();
        $this->container = $container;
    }

    /**
     *
     * @param $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public static function get($id): mixed
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance->container->get($id);
    }
}