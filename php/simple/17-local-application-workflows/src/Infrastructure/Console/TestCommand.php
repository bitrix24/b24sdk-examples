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
use App\Workflow\Activities\ActivityHandlerInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Workflows\Common\DocumentType;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowAutoExecutionType;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
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
                        1 => 'workflow template: list',
                        2 => 'workflow template: add template from file',
                        3 => 'workflow template: delete added template',
                        4 => 'workflow: start',
                        5 => 'workflow: instance list',
                        6 => 'workflow: terminate',
                        7 => 'workflow: kill',
                        8 => 'workflow task: list',
                        9 => 'workflow task: complete',
                        0 => 'exit🚪'
                    ],
                    null
                );
                $question->setErrorMessage('Menu item «%s» is invalid.');
                $menuItem = $helper->ask($input, $output, $question);
                $output->writeln(sprintf('You have just selected: %s', $menuItem));

                switch ($menuItem) {
                    case 'workflow template: list':
                        $output->writeln('fetch template list');

                        $items = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->template()->list()->getTemplates();

                        $table = new Table($output);
                        $table->setHeaders([
                            'ID',
                            'NAME',
                            'MODULE_ID',
                            'ENTITY',
                            'DOCUMENT_TYPE',
                            'AUTO_EXECUTE',
                        ]);
                        foreach ($items as $item) {
                            $table->addRow([
                                $item->ID,
                                $item->NAME,
                                $item->MODULE_ID,
                                $item->ENTITY,
                                implode(' ', $item->DOCUMENT_TYPE),
                                $item->AUTO_EXECUTE->name,
                            ]);
                        }
                        $table->render();
                        break;
                    case 'workflow template: add template from file':
                        $output->writeln('try to add template from file');
                        $addResult = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->template()->add(
                            WorkflowDocumentType::buildForContact(),
                            sprintf('test template for contact %s ', (new \DateTime())->format('Y-m-d H:i:s')),
                            'test template for contact',
                            WorkflowAutoExecutionType::withoutAutoExecution,
                            dirname(__DIR__, 3) . '/workflows/bp.bpt'
                        );

                        $output->writeln(sprintf('<info>added workflow with id %s - OK</info>', $addResult->getId()));
                        break;
                    case 'workflow template: delete added template':
                        $output->writeln('try to delete added template');
                        $ask = new QuestionHelper();
                        $rawBizProcTemplateId = $ask->ask($input, $output, new Question('Enter template Id?' . PHP_EOL));

                        // todo add checks
                        $bizProcTemplateId = (int)$rawBizProcTemplateId;
                        dump($bizProcTemplateId);
                        // try to delete
                        $deleteResult = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->template()->delete(
                            $bizProcTemplateId
                        )->isSuccess();

                        // todo add issue in sdk and api v2
                        // null vs true
                        // https://apidocs.bitrix24.ru/api-reference/bizproc/template/bizproc-workflow-template-delete.html#obrabotka-otveta
                        // https://apidocs.bitrix24.ru/api-reference/crm/deals/crm-deal-delete.html

                        if ($deleteResult) {
                            $output->writeln('<info>template deleted successfully</info>');
                        }
                        break;
                    case 'workflow: start':
                        $output->writeln('try to start workflow');
                        $ask = new QuestionHelper();
                        $rawBizProcTemplateId = $ask->ask($input, $output, new Question('Enter template Id: ' . PHP_EOL));
                        // todo add checks
                        $bizProcTemplateId = (int)$rawBizProcTemplateId;
                        $rawContactId = $ask->ask($input, $output, new Question('Enter contact Id: ' . PHP_EOL));
                        $contactId = (int)$rawContactId;
                        // see https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-workflow-start.html
                        $workflowInstanceId = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->workflow()->start(
                            DocumentType::crmContact,
                            $bizProcTemplateId,
                            $contactId,
                            [
                                'discount_percentage' => 10,
                                'comment' => 'comment from php cli'
                            ]
                        )->getRunningWorkflowInstanceId();
                        dump($workflowInstanceId);
                        break;
                    case 'workflow: instance list':
                        // see https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-workflow-instances.html
                        $output->writeln('workflow: instance list');
                        $items = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->workflow()->instances()->getInstances();
                        $table = new Table($output);
                        $table->setHeaders([
                            'workflow instance ID',
                            'ENTITY',
                            'MODULE_ID',
                            'DOCUMENT_ID',
                            'TEMPLATE_ID',
                            'STARTED'
                        ]);
                        foreach ($items as $item) {
                            $table->addRow([
                                $item->ID,
                                $item->ENTITY,
                                $item->MODULE_ID,
                                $item->DOCUMENT_ID,
                                $item->TEMPLATE_ID,
                                $item->STARTED?->format('Y-m-d H:i:s'),
                            ]);
                        }
                        $table->render();
                        break;
                    case 'workflow: terminate':
                        $output->writeln([
                            'workflow: terminate',
                            'This method stops the specified workflow. All data related to the workflow will be preserved.',
                            'https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-workflow-terminate.html'
                        ]);

                        $ask = new QuestionHelper();
                        $rawWorkflowItemId = $ask->ask($input, $output, new Question('Enter workflow Id: ' . PHP_EOL));

                        $result = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->workflow()->terminate(
                            $rawWorkflowItemId,
                            'terminate workflow from cli'
                        )->isSuccess();
                        if ($result) {
                            $output->writeln('<info>workflow terminated successfully</info>');
                        }
                        break;
                    case 'workflow: kill':
                        $output->writeln([
                            'workflow: kill',
                            'This method deletes the running workflow along with all process data.',
                            'https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-workflow-kill.html'
                        ]);

                        $ask = new QuestionHelper();
                        $rawWorkflowItemId = $ask->ask($input, $output, new Question('Enter workflow Id: ' . PHP_EOL));

                        $result = $this->b24ServiceBuilderFactory::createFromStoredToken()->getBizProcScope()->workflow()->kill(
                            $rawWorkflowItemId
                        )->isSuccess();
                        if ($result) {
                            $output->writeln('<info>workflow killed successfully</info>');
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
