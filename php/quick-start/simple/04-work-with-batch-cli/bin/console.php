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

use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\EmailValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\PhoneValueType;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\SingleCommandApplication;
use League\Csv\Writer;

(new SingleCommandApplication())
    ->addOption('webhook', null, InputOption::VALUE_REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $filename = 'import.csv';
        $demoContactsCount = 3000;
        // check call arguments
        $b24Webhook = (string)$input->getOption('webhook');
        if ($b24Webhook === '') {
            $output->writeln([
                    '',

                    '<error>you must set option «--webhook» with your webhook</error>',
                    ''
                ]
            );
            return Command::FAILURE;
        }
        try {
            // try to connect to bitrix24 portal with webhook credentials
            $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook($b24Webhook);
            $b24UserProfile = $b24Service->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();
            $output->writeln([
                sprintf('<info>successful connect to «%s» portal</info>', parse_url($b24Webhook, PHP_URL_HOST)),
                sprintf(
                    'current user – %s | %s %s',
                    $b24UserProfile->ID,
                    $b24UserProfile->NAME,
                    $b24UserProfile->LAST_NAME
                ),
                ''
            ]);

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
                        2 => 'import contacts from file to Bitrix24',
                        3 => 'export contacts from Bitrix24 to file',
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
                                    '<info>generated %s demo-contacts and saved in file «%s» for import to Bitrix24</info>',
                                    count($items),
                                    $filename
                                ),
                                ''
                            ]
                        );
                        break;
                    case 'import contacts from file to Bitrix24':
                        $fs = new Symfony\Component\Filesystem\Filesystem();
                        if (!$fs->exists($filename)) {
                            $output->writeln([
                                    '',
                                    sprintf(
                                        '<error>file «%s» not found, you must call «generate demo-file with contacts» menu item</error>',
                                        $filename
                                    ),
                                    ''
                                ]
                            );
                            break;
                        }
                        // read data from file
                        $reader = Reader::createFromPath($filename, 'r');
                        $reader->setHeaderOffset(0);
                        $records = $reader->getRecords();

                        // convert flat row to bitrix24 contact data structure
                        $b24Contacts = [];
                        foreach ($records as $fileRow) {
                            $b24Contacts[] = [
                                'NAME' => $fileRow['name'],
                                'SECOND_NAME' => $fileRow['second_name'],
                                'EMAIL' => [
                                    'VALUE' => $fileRow['email'],
                                    'VALUE_TYPE' => EmailValueType::work
                                ],
                                'PHONE' => [
                                    'VALUE' => $fileRow['phone'],
                                    'VALUE_TYPE' => PhoneValueType::work
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
                        foreach ($b24Service->getCRMScope()->contact()->batch->add($b24Contacts) as $addContactResult) {
                            $progressBar->setMessage(sprintf('last added contact id - %s', $addContactResult->getId()));
                            $progressBar->advance();
                        }
                        $progressBar->finish();
                        $output->writeln(['', '<info>Contacts successfully added to Bitrix24 </info>']);
                        break;
                    case 'export contacts from Bitrix24 to file':
                        $contactsInCrmCount = $b24Service->getCRMScope()->contact()->countByFilter();
                        $output->writeln(
                            [
                                '',
                                sprintf(
                                    'contacts count in CRM - %s items',
                                    $contactsInCrmCount
                                ),
                                '',
                                'try to export contacts to file...'
                            ]
                        );


                        $writer = Writer::createFromPath(
                            sprintf('b24export-%s.csv', (new DateTime())->format('Y-m-d-H-i-s')),
                            'w+'
                        );
                        $writer->insertOne(['id', 'name', 'second_name', 'phone', 'email']);
                        ProgressBar::setFormatDefinition(
                            'custom',
                            " %message%\n%current%/%max% [%bar%] %percent:3s%% \n  %elapsed:6s%/%estimated:-6s% %memory:6s%"
                        );


                        $exportContactCnt = min($contactsInCrmCount, $demoContactsCount);
                        $progressBar = new ProgressBar($output, $exportContactCnt);
                        $progressBar->setFormat('custom');
                        $progressBar->setMessage('wait for first batch call response...');
                        $progressBar->start();
                        foreach (
                            $b24Service->getCRMScope()->contact()->batch->list([],
                                [],
                                ['ID', 'NAME', 'SECOND_NAME', 'PHONE', 'EMAIL'],
                                $exportContactCnt) as $contact
                        ) {
                            $writer->insertOne([
                                $contact->ID,
                                $contact->NAME,
                                $contact->SECOND_NAME,
                                // todo pretty print phones
                                print_r($contact->PHONE, true),
                                print_r($contact->EMAIL, true),
                            ]);
                            $progressBar->advance();
                        }
                        $progressBar->finish();

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