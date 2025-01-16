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

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\SingleCommandApplication;
use League\Csv\Writer;

(new SingleCommandApplication())
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        try {
            while (true) {
                /**
                 * @var QuestionHelper $helper
                 *
                 * method «setCode» override «execute» method for object Command
                 * we use SingleCommandApplication for reduce code in this example
                 */
                // @phpstan-ignore-next-line
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Please select command',
                    [
                        1 => 'generate demo-file with contacts',
                        2 => 'load contacts from file to Bitrix24',
                        3 => 'save contacts from Bitrix24 to file',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'generate demo-file with contacts':
                        // create demo contacts with full name, phone, email
                        $faker = Faker\Factory::create('en_EN');
                        $writer = Writer::createFromPath('data.csv', 'w+');
                        $writer->insertOne(['name', 'second_name', 'phone', 'email']);

                        $items = [];
                        for ($i = 0; $i < 10; $i++) {
                            $items[] = [
                                $faker->firstName(),
                                $faker->lastName(),
                                $faker->phoneNumber(),
                                $faker->email()
                            ];
                        }
                        $writer->insertAll($items);

                        break;
                    case 'load contacts from file to Bitrix24':
                        break;
                    case 'save contacts from Bitrix24 to file':
                        break;
                    case 'exit🚪':
                        return Command::SUCCESS;
                }
            }
        } catch (Throwable $exception) {
            $output->writeln(sprintf('<error>ERROR: %s</error>', $exception->getMessage()));
            $output->writeln(sprintf('<info>DETAILS: %s</info>', $exception->getTraceAsString()));

            return Command::FAILURE;
        }
    })
    ->run();