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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Writer;
use Faker;

#[AsCommand(
    name: 'b24:data-generator',
    description: 'Generate demo data',
    hidden: false
)]
class DataGeneratorCommand extends Command
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
        $this->logger->debug('Command.DataGeneratorCommand.start');

        $output->writeln('Start Generate demo data...');

        $filename = '/var/tmp/import.csv';
        $demoContactsCount = 7000;

        $faker = Faker\Factory::create('en_EN');
        $writer = Writer::createFromPath($filename, 'w+');
        $writer->insertOne(['name', 'second_name', 'phone', 'email']);

        $items = [];
        for ($i = 0; $i < $demoContactsCount; $i++) {
            $items[] = [
                $faker->firstName(),
                $faker->lastName(),
                $faker->e164PhoneNumber(),
                $faker->email()
            ];
        }
        $writer->insertAll($items);
        $output->writeln([
                '',
                sprintf(
                    '<info>Successfully generated %s demo-contacts and saved in «volumes» folder</info>',
                    $demoContactsCount,
                ),
                ''
            ]
        );

        $this->logger->debug('Command.DataGeneratorCommand.finish');

        return self::SUCCESS;
    }
}