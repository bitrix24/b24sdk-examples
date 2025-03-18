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

use Bitrix24\SDK\Core\Exceptions\WrongConfigurationException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;

readonly class Bootstrap
{
    private const string CONFIG_FILE_NAME = '/config/.env';

    /**
     * Loads configuration from the environment file.
     *
     * @throws WrongConfigurationException if "symfony/dotenv" is not added as a Composer dependency.
     */
    public static function loadConfigFromEnvFile(): void
    {
        static $isConfigLoaded = null;
        if ($isConfigLoaded === null) {
            if (!class_exists(Dotenv::class)) {
                throw new WrongConfigurationException('You need to add "symfony/dotenv" as Composer dependencies.');
            }

            $argvInput = new ArgvInput();
            if (null !== $env = $argvInput->getParameterOption(['--env', '-e'], null, true)) {
                putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
            }

            if ($argvInput->hasParameterOption('--no-debug', true)) {
                putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
            }

            (new Dotenv())->bootEnv(dirname(__DIR__) . self::CONFIG_FILE_NAME);

            $isConfigLoaded = true;
        }
    }
}