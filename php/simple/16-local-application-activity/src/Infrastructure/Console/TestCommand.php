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

use App\Bitrix24ServiceBuilderFactory;
use App\Robots\RobotHandlerInterface;
use App\Workflow\Activities\ActivityHandlerInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Main\Common\EventHandlerMetadata;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
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
        /**
         * @var $handlers ActivityHandlerInterface[]
         */
        private readonly iterable $handlers,
        private readonly Bitrix24ServiceBuilderFactory $b24ServiceBuilderFactory,
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
            $user = $this->b24ServiceBuilderFactory::createFromStoredToken()->getMainScope()->main()->getCurrentUserProfile()->getUserProfile();

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
                        1 => 'Get activity list',
                        2 => 'Install activity',
                        3 => 'Uninstall activity',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'Get activity list':
                        $items = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->activity()->list();
                        dump($items->getActivities());
                        break;
                    case 'Install activity':
                        var_dump('Install activity');

                        foreach ($this->handlers as $handler) {
                            try {
                                $installMetadata = $handler->getInstallMetadata(
                                //todo pass handler url from config
                                    'https://7dce-193-34-225-43.ngrok-free.app/crm-activity-handler.php',
                                    $user->ID,
                                );
                                // todo add to batch
                                $installResult = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->activity()->add(
                                    $installMetadata->activityCode,
                                    $installMetadata->handlerUrl,
                                    $installMetadata->b24AuthUserId,

                                    $installMetadata->name,
                                    // todo add to metadata
                                    [],
                                    $installMetadata->isUseSubscription,
                                    $installMetadata->properties,
                                    $installMetadata->isUsePlacement,
                                    $installMetadata->returnProperties,
                                    // todo add to metadata
                                    WorkflowDocumentType::buildForDeal(),
                                    // todo add to metadata
                                    // todo check filter and document type
                                    []
                                );
                                var_dump($installResult->isSuccess());
                            } catch (Throwable $exception) {
                                $this->logger->error('Install crm-robots error', [
                                    'exception' => $exception,
                                ]);
                                var_dump($exception->getMessage());
                                //var_dump($exception->getTrace());
                            }
                        }
                        var_dump('Install crm-robots finish');
                        break;
                    case 'Uninstall activity':
                        var_dump('Uninstall activity');

                        foreach ($this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->activity()->list()->getActivities() as $activityCode) {
                            $uninstallResult = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->activity()->delete($activityCode);
                            dump($uninstallResult->isSuccess());
                        }
                        var_dump('Uninstall OK');
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
