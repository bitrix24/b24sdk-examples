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

namespace App\Scoring\Infrastructure\Console;

use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelMapper;
use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelQueries;
use App\Scoring\Infrastructure\Bitrix24\ScoreCommands;
use App\Scoring\Infrastructure\Bitrix24\ScoreFieldMapper;
use App\Scoring\Infrastructure\Bitrix24\ScoreQueries;
use App\Scoring\RiskLevel;
use App\Scoring\Services\ScoringProcessor;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'b24:process-contact',
    description: 'Score contact and fill score field to contact',
    hidden: false
)]
class ScoreContactCommand extends Command
{
    private bool $isShouldStopWork = false;

    #[Override]
    public function getSubscribedSignals(): array
    {
        return [
            SIGINT, // Interrupt
            SIGTERM // Terminate
        ];
    }

    #[Override]
    public function handleSignal(int $signal, int|false $previousExitCode = 0): false|int
    {
        $this->isShouldStopWork = true;

        return parent::handleSignal($signal, $previousExitCode);
    }

    public function __construct(
        private readonly RiskLevelQueries $riskLevelQueries,
        private readonly ScoreQueries $scoreQueries,
        private readonly ScoreCommands $scoreCommands,
        private readonly ScoreFieldMapper $scoreFieldMapper,
        private readonly RiskLevelMapper $riskLevelMapper,
        private readonly ScoringProcessor $scoringProcessor,
        private readonly LoggerInterface $logger,
    ) {
        // best practices recommend calling the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug('Command.ScoreContactCommand.start');
        $ss = new SymfonyStyle($input, $output);

        $b24ServiceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL'],
            null,
            $this->logger
        );

        // load entity type id for risk level for the current portal
        $riskLevelsEntityTypeId = $this->riskLevelMapper->getEntityTypeId($b24ServiceBuilder);

        // load risk levels as smart process items for the current portal
        $indexedRiskLevels = $this->riskLevelQueries->getRiskLevels($b24ServiceBuilder, $riskLevelsEntityTypeId);
        var_dump($indexedRiskLevels);

        // create contact
        $b24ContactId = $b24ServiceBuilder->getCRMScope()->contact()->add([
            'NAME' => 'Test contact3',
            'SECOND_NAME' => 'fffff'
        ])->getId();

        // filter contacts without risk level
        $contactsToScore = $this->scoreQueries->filterContactsWithoutRiskLevel(
            $b24ServiceBuilder,
            [],
            [
                'ID' => $b24ContactId,
            ]
        )->getContacts();

        foreach ($contactsToScore as $contact) {
            // score contact by our super-duper smart scoring model
            $score = $this->scoringProcessor->scorePerson([
                'NAME' => $contact->NAME . '1',
                'LAST_NAME' => $contact->LAST_NAME,
                'SECOND_NAME' => $contact->SECOND_NAME,
            ]);

            // save score to contact
            // todo add batch support
            $this->scoreCommands->setScore($b24ServiceBuilder, $indexedRiskLevels, $contact->ID, $score);

            $ss->writeln(sprintf('Contact processed: %s | %s | score %s ', $contact->ID, $score->risk->value, $score->scores));
        }


        $contacts = $this->scoreQueries->filterContactsWithRiskLevel(
            $b24ServiceBuilder,
            RiskLevel::HIGH,
            [],
            [],
            ['*', 'UF_*'],
        )->getContacts();


        foreach ($contacts as $contact) {
            $ss->writeln(
                sprintf(
                    '%s | %s | %s | %s',
                    $contact->ID,
                    $contact->NAME,
                    $contact->getUserfieldByFieldName($this->scoreFieldMapper->getName()),
                    $contact->getSmartProcessItem($riskLevelsEntityTypeId),
                )
            );
        }

//        var_dump($contacts->getContacts());


        return self::SUCCESS;
    }
}
