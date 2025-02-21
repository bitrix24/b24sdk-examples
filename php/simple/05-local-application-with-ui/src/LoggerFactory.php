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

namespace App;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    private const LOG_FILE_NAME = '/var/logs/application.log';

    /**
     * @throws WrongConfigurationException
     * @throws InvalidArgumentException
     */
    public static function create(): LoggerInterface
    {
        static $logger;

        if ($logger === null) {
            // check settings
            if (!array_key_exists('BITRIX24_PHP_SDK_LOG_LEVEL', $_ENV)) {
                throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_LEVEL');
            }

            // rotating
            $rotatingFileHandler = new RotatingFileHandler(
                dirname(__DIR__) . self::LOG_FILE_NAME,
                0,
                (int)$_ENV['BITRIX24_PHP_SDK_LOG_LEVEL']
            );
            $rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');

            $logger = new Logger('App');
            $logger->pushHandler($rotatingFileHandler);
            $logger->pushProcessor(new MemoryUsageProcessor(true, true));
            $logger->pushProcessor(new UidProcessor());
        }

        return $logger;
    }
}