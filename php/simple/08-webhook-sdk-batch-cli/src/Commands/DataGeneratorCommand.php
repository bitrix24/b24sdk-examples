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

        $output->writeln('Generate demo data...');

        $this->logger->debug('Command.DataGeneratorCommand.finish');

        return self::SUCCESS;
    }
}