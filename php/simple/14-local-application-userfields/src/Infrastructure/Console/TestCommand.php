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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Main\Common\EventHandlerMetadata;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'b24:test-command',
    description: 'test cli command',
    hidden: false
)]
class TestCommand extends Command
{
    public function __construct(
        private readonly ServiceBuilder $b24ServiceBuilder,
        private readonly LoggerInterface $logger
    ) {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Command.TestCommand.start');

        $symfonyStyle = new SymfonyStyle($input, $output);
        try {
            $symfonyStyle->writeln(['Hello world!', '', 'start request to bitrix24 with saved token']);
            $user = $this->b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();

            $symfonyStyle->writeln(
                sprintf(
                    'user info: %s',
                    sprintf(
                        'user id – %s' . PHP_EOL .
                        'user name - %s %s' . PHP_EOL .
                        'is admin - %s',
                        $user->ID,
                        $user->NAME,
                        $user->LAST_NAME,
                        $user->ADMIN ? 'yes' : 'no'
                    )
                )
            );

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
                        1 => 'install userfields to contact',
                        2 => 'get contact userfields',
                        3 => 'get contact',
                        4 => 'add contact with userfields',
                        5 => 'add new user field type',
                        6 => 'get usertype list',
                        7 => 'add userfield with custom type',
                        8 => 'add contact with custom type data',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'install userfields to contact':
                        dump('install userfields to contact');

                        $userFieldId = $this->b24ServiceBuilder->getCRMScope()->contactUserfield()->add(
                            [
                                'USER_TYPE_ID'=>'string',
                                'FIELD_NAME' =>'t1_field',
                                'LABEL' =>'Field name',
                                'XML_ID' =>'field_xml_id',
                            ]
                        )->getId();
                        dump($userFieldId);




                        break;
                    case 'get contact':
                        dump('get contact');

                        $contact = $this->b24ServiceBuilder->getCRMScope()->contact()->get(173374)->contact();
                        dump($contact);
                        dump($contact->getUserfieldByFieldName('T1_FIELD'));

                        break;
                    case 'get contact userfields':
                        dump('get contact userfields');

                        $contactFields = $this->b24ServiceBuilder->getCRMScope()->contactUserfield()->list([],[])->getUserfields();
                        dump($contactFields);

                        break;
                    case 'add contact with userfields':
                        dump('add contact with userfields');

                        $newContactData = [
                            'NAME' => 'test contact',
                            'UF_CRM_T1_FIELD' =>'user field value',
                        ];

                        $b24ContactId = $this->b24ServiceBuilder->getCRMScope()->contact()->add($newContactData)->getId();
                        dump($b24ContactId);

                        $addedContact = $this->b24ServiceBuilder->getCRMScope()->contact()->get($b24ContactId)->contact();
                        dump($addedContact);

                        break;
                    case 'add new user field type':
                        $res =  $this->b24ServiceBuilder->core->call('userfieldtype.add',[
                            'USER_TYPE_ID'=>'custom_type',
                            'HANDLER' => 'https://7e62-212-112-118-34.ngrok-free.app/uf-type-handler.php',
                            'TITLE'=>'Custom type',
                            'DESCRIPTION'=>'Custom type description',
                        ]);
                        dump($res->getResponseData()->getResult());
                        break;
                    case 'get usertype list':

                       // $fields = $this->b24ServiceBuilder->core->call('userfieldtype.list')->getResponseData()->getResult();

                        $fields = $this->b24ServiceBuilder->getPlacementScope()->userfieldtype()->list()->getUserFieldTypes();
                        dump($fields);

                        break;
                    case 'add userfield with custom type':
                        dump('add userfield with custom type to contact');

                        $userFieldId = $this->b24ServiceBuilder->getCRMScope()->contactUserfield()->add(
                            [
                                'USER_TYPE_ID'=>'custom_type',
                                'FIELD_NAME' =>'t2_field',
                                'LABEL' =>'Custom type Field name',
                                'XML_ID' =>'field_t2_xml_id',
                            ]
                        )->getId();
                        dump($userFieldId);


                        break;
                    case 'add contact with custom type data':
                        $newContactData = [
                            'NAME' => 'test contact',
                            'UF_CRM_T2_FIELD' => json_encode([
                                'key1' => 'value1',
                                'key2' => 'value2',
                            ], JSON_THROW_ON_ERROR),
                        ];

                        $b24ContactId = $this->b24ServiceBuilder->getCRMScope()->contact()->add($newContactData)->getId();
                        dump($b24ContactId);

                        $addedContact = $this->b24ServiceBuilder->getCRMScope()->contact()->get($b24ContactId)->contact();
                        dump($addedContact);


                        break;
                    case 'exit🚪':
                        return Command::SUCCESS;
                }
            }
        } catch (BaseException $exception) {
            $symfonyStyle->caution('Bitrix24 error');
            $symfonyStyle->text(
                [
                    $exception->getMessage(),
                ]
            );
        } catch (Throwable $exception) {
            $symfonyStyle->caution('fatal error');
            $symfonyStyle->text(
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
