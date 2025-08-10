<?php

/**
 * This file is part of the b24sdk-examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Commands;

use App\Scoring\Infrastructure\Bitrix24\ScoreFieldsMapper;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\EmailValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\PhoneValueType;

#[AsCommand(
    name: 'b24:install',
    description: 'Install application fields',
    hidden: false
)]
class InstallCommand extends Command
{
    private bool $isShouldStopWork = false;

    #[Override]
    public function getSubscribedSignals(): array
    {
        return [
            SIGINT, // Interrupt
            SIGTERM // Terminate
        ];
    }

    #[Override]
    public function handleSignal(int $signal, int|false $previousExitCode = 0): false|int
    {
        $this->isShouldStopWork = true;

        return parent::handleSignal($signal, $previousExitCode);
    }

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ScoreFieldsMapper $b24ScoreFieldsMapper
    ) {
        // best practices recommend calling the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Command.InstallCommand.start');
        $symfonyStyle = new SymfonyStyle($input, $output);

        $b24ServiceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
            null,
            $this->logger
        );

        // add contacts to bitrix24 via batch call
        $symfonyStyle->writeln(['Start adding fields to contacts...', '']);


        $this->b24ScoreFieldsMapper->installFields($b24ServiceBuilder);


        $this->logger->debug('Command.InstallCommand.finish');

        return self::SUCCESS;
    }
}
