<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Infrastructure\Console;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'b24:test-command',
    description: 'test cli command',
    hidden: false
)]
class TestCommand extends Command
{
    protected CoreInterface $core;

    /**
     * ListCommand constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Command.TestCommand.start');

        $io = new SymfonyStyle($input, $output);
        try {
            $io->writeln('Hello world!');
        } catch (BaseException $exception) {
            $io->caution('Bitrix24 error');
            $io->text(
                [
                    sprintf('%s', $exception->getMessage()),
                ]
            );
        } catch (Throwable $exception) {
            $io->caution('fatal error');
            $io->text(
                [
                    $exception->getMessage(),
                    $exception->getTraceAsString(),
                ]
            );
        }
        $this->logger->debug('Command.TestCommand.finish');

        return self::SUCCESS;
    }

}
