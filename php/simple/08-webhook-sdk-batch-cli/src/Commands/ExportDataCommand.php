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

use Bitrix24\SDK\Services\ServiceBuilderFactory;
use DateTime;
use League\Csv\Writer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'b24:export-data',
    description: 'Export demo data from Bitrix24',
    hidden: false
)]
class ExportDataCommand extends Command
{
    private bool $isShouldStopWork = false;

    #[\Override]
    public function getSubscribedSignals(): array
    {
        return [
            SIGINT, // Interrupt
            SIGTERM // Terminate
        ];
    }

    #[\Override]
    public function handleSignal(int $signal, int|false $previousExitCode = 0): false|int
    {
        $this->isShouldStopWork = true;

        return parent::handleSignal($signal, $previousExitCode);
    }

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Command.ExportDataCommand.start');
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->writeln('Start export demo data from Bitrix24...');

        $serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
            null,
            $this->logger
        );

        $contactsInCrmCount = $serviceBuilder->getCRMScope()->contact()->countByFilter();
        if ($contactsInCrmCount < 3000) {
            $symfonyStyle->warning([
                'Contacts count in CRM is less than 3000, export will be aborted',
                'use command "b24:import-data" to import more demo data',
            ]);

            return self::SUCCESS;
        }

        $symfonyStyle->info(sprintf('contacts count in CRM - %s items', $contactsInCrmCount));
        $exportFilename = sprintf('/var/tmp/b24export-%s.csv', (new DateTime())->format('Y-m-d-H-i-s'));
        $writer = Writer::createFromPath(
            $exportFilename,
            'w+'
        );
        $writer->insertOne(['id', 'name', 'second_name', 'phone', 'email']);
        ProgressBar::setFormatDefinition(
            'custom',
            " %message%\n%current%/%max% [%bar%] %percent:3s%% \n  %elapsed:6s%/%estimated:-6s% %memory:6s%"
        );

        $progressBar = new ProgressBar($output, $contactsInCrmCount);
        $progressBar->setFormat('custom');
        $progressBar->setMessage('wait for first batch call response...');
        $progressBar->start();
        // batch call to bitrix24, data read with 2500 elements per one api-call
        foreach (
            $serviceBuilder->getCRMScope()->contact()->batch->list(
                [],
                [],
                ['ID', 'NAME', 'SECOND_NAME', 'PHONE', 'EMAIL'],
                $contactsInCrmCount
            ) as $contact
        ) {
            // process POSIX signals and gracefully shutdown long-running task
            if ($this->isShouldStopWork) {
                $symfonyStyle->caution('Process interrupted, try to gracefully shutdown long-running task...');

                return self::FAILURE;
            }

            $progressBar->setMessage(sprintf('last exported contact id - %s', $contact->ID));
            $writer->insertOne([
                $contact->ID,
                $contact->NAME,
                $contact->SECOND_NAME,
                // phone and email are multiple properties, that's why we need to concatenate items before export if records are multiple
                implode(', ', array_column($contact->PHONE, 'VALUE')),
                implode(', ', array_column($contact->EMAIL, 'VALUE'))
            ]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->info('Contacts successfully exported from Bitrix24 to «volumes» folder');

        $this->logger->debug('Command.ExportDataCommand.finish');

        return self::SUCCESS;
    }
}
