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
use Carbon\CarbonImmutable;
use DateTimeImmutable;
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
    name: 'b24:import-data',
    description: 'Import demo data to Bitrix24',
    hidden: false
)]
class ImportDataCommand extends Command
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
        // best practices recommend calling the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Command.ImportDataCommand.start');
        $symfonyStyle = new SymfonyStyle($input, $output);

        $serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
            null,
            $this->logger
        );

        $filename = '/var/tmp/demo-data.csv';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($filename)) {
            $symfonyStyle->error(sprintf('file «%s» not found in folder «volumes», you must call «make php-cli-generate-demo-data»', basename($filename)));
            return self::FAILURE;
        }

        $reader = Reader::createFromPath($filename, 'r');
        $reader->setHeaderOffset(0);

        $records = $reader->getRecords();
        // convert flat row to bitrix24 contact data structure
        $b24Contacts = [];
        // read data from a file into memory
        foreach ($records as $record) {
            // accumulate data into array
            $b24Contacts[] = [
                'NAME' => $record['name'],
                'SECOND_NAME' => $record['second_name'],
                'EMAIL' => [
                    [
                        'VALUE' => $record['email'],
                        'VALUE_TYPE' => EmailValueType::work->value
                    ]
                ],
                'PHONE' => [
                    [
                        'VALUE' => $record['phone'],
                        'VALUE_TYPE' => PhoneValueType::work->value
                    ]
                ]
            ];
        }

        // add contacts to bitrix24 via batch call
        $symfonyStyle->writeln(['Start adding contacts to Bitrix24 via batch call...', '']);
        ProgressBar::setFormatDefinition(
            'custom',
            " %message%\n%current%/%max% [%bar%] %percent:3s%% \n  %elapsed:6s%/%estimated:-6s% %memory:6s%"
        );
        $progressBar = new ProgressBar($output, count($b24Contacts));
        $progressBar->setFormat('custom');
        $progressBar->setMessage('wait for first batch call response...');
        $progressBar->start();

        // batch call crm.contact.add with 50 elements per one api-call
        foreach ($serviceBuilder->getCRMScope()->contact()->batch->add($b24Contacts) as $addContactResult) {
            // process POSIX signals and gracefully shutdown long-running task
            if ($this->isShouldStopWork) {
                $symfonyStyle->caution('Process interrupted, try to gracefully shutdown long-running task...');

                return self::FAILURE;
            }

            // Process throttling information from bitrix24 api
            if ($addContactResult->getResponseData()->getTime()->operatingResetAt !== null &&
                $addContactResult->getResponseData()->getTime()->operating !== null) {
                // see https://apidocs.bitrix24.com/limits.html
                $operatingThreshold = 400;
                $operatingWaitTimeout = 10;
                $currentOperatingValue = round($addContactResult->getResponseData()->getTime()->operating);
                $operatingResetAt = CarbonImmutable::createFromTimestamp($addContactResult->getResponseData()->getTime()->operatingResetAt);
                $operatingWait = round($operatingResetAt->diffInSeconds(CarbonImmutable::now()));
                if ($currentOperatingValue > $operatingThreshold) {
                    $symfonyStyle->writeln(['', '', sprintf('(⊙_⊙) oh senpai your operating value is too high - %s!', $currentOperatingValue)]);
                    $symfonyStyle->info(sprintf('We must wait for %s seconds before next batch call...', $operatingWaitTimeout));

                    $start = time();
                    while ((time() - $start) < $operatingWaitTimeout) {
                        $symfonyStyle->write(sprintf("\rWaiting... %d seconds remaining ", $operatingWaitTimeout - (time() - $start)));
                        usleep(200000);
                    }

                    $symfonyStyle->write("\r");
                }

                $progressBar->setMessage(
                    sprintf(
                        'last added contact id - %s | operating - %s seconds, reset after %s seconds',
                        $addContactResult->getId(),
                        $currentOperatingValue,
                        $operatingWait
                    )
                );
            } else {
                $progressBar->setMessage(sprintf('last added contact id - %s', $addContactResult->getId()));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(['', '<info>Contacts successfully added to Bitrix24 </info>']);


        $this->logger->debug('Command.ImportDataCommand.finish');

        return self::SUCCESS;
    }
}
