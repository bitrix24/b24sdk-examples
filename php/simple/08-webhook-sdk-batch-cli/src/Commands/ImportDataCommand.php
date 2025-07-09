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

require_once 'vendor/autoload.php';

use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Reader;
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
        $this->logger->debug('Command.ImportDataCommand.start');

        $output->writeln('Start import demo data to Bitrix24...');

        $serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
            null,
            $this->logger
        );

        $filename = '/var/tmp/demo-data.csv';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($filename)) {
            $output->writeln(
                [
                    '',
                    sprintf(
                        '<error>file «%s» not found, you must call «make php-cli-generate-demo-data»</error>',
                        $filename
                    ),
                    ''
                ]
            );
        }

        // read data from file
        $reader = Reader::createFromPath($filename, 'r');
        $reader->setHeaderOffset(0);

        $records = $reader->getRecords();

        // convert flat row to bitrix24 contact data structure
        $b24Contacts = [];
        foreach ($records as $record) {
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
        $output->writeln(['start adding contacts to Bitrix24 via batch call...', '']);
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
            $progressBar->setMessage(
                sprintf(
                    'last added contact id - %s | operating - %s seconds',
                    $addContactResult->getId(),
                    // see https://apidocs.bitrix24.com/limits.html
                    round($addContactResult->getResponseData()->getTime()->operating, 2)
                )
            );
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(['', '<info>Contacts successfully added to Bitrix24 </info>']);


        $this->logger->debug('Command.ImportDataCommand.finish');

        return self::SUCCESS;
    }
}
