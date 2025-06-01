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
                        1 => 'Get crm-robots list',
                        2 => 'Install crm-robots',
                        3 => 'Uninstall crm-robots',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'Get crm-robots list':
                        $items = $this->b24ServiceBuilder->getBizProcScope()->robot()->list();
                        dump($items->getRobots());
                        break;
                    case 'Install crm-robots':
                        $b24UserId = $this->b24ServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;


                        $installResult = $this->b24ServiceBuilder->getBizProcScope()->robot()->add(
                            'weather_robot',
                            'https://cb99-193-34-225-254.ngrok-free.app/crm-robot-handler-simple.php',
                            $b24UserId,
                            [
                                'ru' => 'Тестовый робот 1'
                            ],
                            true,
                            [
                                'city' => [
                                    'name' => [
                                        'ru' => 'Город'
                                    ],
                                    'type' => 'string',
                                ],
                                'country' => [
                                    'name' => [
                                        'ru' => 'Страна'
                                    ],
                                    'type' => 'string',
                                ]
                            ],
                            false,
                            [
                                'temperature' => [
                                    'name' => [
                                        'ru' => 'Температура',
                                    ],
                                    'type' => 'int',
                                ]
                            ]
                        );
                        dump($installResult->isSuccess());


                        var_dump('Install crm-robots');
                        break;
                    case 'Uninstall crm-robots':
                        var_dump('Uninstall crm-robots');

                        foreach ($this->b24ServiceBuilder->getBizProcScope()->robot()->list()->getRobots() as $robot) {
                            $uninstallResult = $this->b24ServiceBuilder->getBizProcScope()->robot()->delete($robot);
                            dump($uninstallResult->isSuccess());
                        }




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
