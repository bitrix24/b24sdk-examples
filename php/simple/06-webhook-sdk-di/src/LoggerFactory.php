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

final readonly class LoggerFactory
{
    private const string FILE_NAME = '/var/logs/app.log';

    private const string LOGGER_NAME = 'app';

    /**
     * @throws WrongConfigurationException
     * @throws InvalidArgumentException
     */
    public static function create(?string $loggerName = null): LoggerInterface
    {
        static $logger;

        if ($logger === null) {
            // check settings
            if (!array_key_exists('BITRIX24_PHP_SDK_LOG_LEVEL', $_ENV)) {
                throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_LEVEL');
            }

            if (!array_key_exists('BITRIX24_PHP_SDK_LOG_MAX_FILES_COUNT', $_ENV)) {
                throw new InvalidArgumentException('in $_ENV variables not found key BITRIX24_PHP_SDK_LOG_MAX_FILES_COUNT');
            }

            // rotating
            $rotatingFileHandler = new RotatingFileHandler(
                dirname(__DIR__) . self::FILE_NAME,
                (int)$_ENV['BITRIX24_PHP_SDK_LOG_MAX_FILES_COUNT'],
                Logger::toMonologLevel($_ENV['BITRIX24_PHP_SDK_LOG_LEVEL'])
            );
            $rotatingFileHandler->setFilenameFormat('{filename}-{date}', 'Y-m-d');

            $logger = new Logger($loggerName ?? self::LOGGER_NAME);
            $logger->pushHandler($rotatingFileHandler);
            $logger->pushProcessor(new MemoryUsageProcessor(true, true));
            $logger->pushProcessor(new UidProcessor());

            return $logger;
        }

        if ($loggerName === null) {
            return $logger->withName(self::LOGGER_NAME);
        }

        return $logger->withName($loggerName);
    }
}
