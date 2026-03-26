<?php

namespace App\Tests\Integration\Scoring\Infrastructure\Bitrix24;

use App\LoggerFactory;
use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelInstaller;
use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelMapper;
use App\Scoring\Infrastructure\Bitrix24\RiskLevels\RiskLevelQueries;
use App\Scoring\Infrastructure\Bitrix24\ScoreFieldMapper;
use App\Scoring\Infrastructure\Bitrix24\ScoreInstaller;
use App\Scoring\Infrastructure\Bitrix24\ScoreQueries;
use App\Scoring\RiskLevel;
use App\Scoring\Score;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ScoreQueries::class)]
class ScoreQueriesTest extends TestCase
{
    private LoggerInterface $logger;
    private ServiceBuilder $sb;
    private ScoreQueries $scoreQueries;
    private RiskLevelMapper $riskLevelMapper;

    #[TestDox('Test filterContactsWithoutRiskLevel')]
    public function testFilterContactsWithoutRiskLevel(): void
    {
        $beforeCnt = $this->scoreQueries->filterContactsWithoutRiskLevel(
            $this->sb,
        )->getCoreResponse()->getResponseData()->getPagination()->getTotal();

        $xmlId = Uuid::v7()->toRfc4122();
        $this->sb->getCRMScope()->contact()->add([
            'NAME' => sprintf('test name %s', time()),
            'ORIGIN_ID' => $xmlId
        ]);
        $this->assertEquals(
            $beforeCnt + 1,
            $this->scoreQueries->filterContactsWithoutRiskLevel($this->sb)->getCoreResponse()->getResponseData()->getPagination()->getTotal()
        );

        $contact = $this->scoreQueries->filterContactsWithoutRiskLevel(
            $this->sb,
            [],
            [
                'ORIGIN_ID' => $xmlId
            ]
        )->getContacts()[0];

        // load entity type id for risk level for the current portal
        $riskLevelsEntityTypeId = $this->riskLevelMapper->getEntityTypeId($this->sb);

        $this->assertNull($contact->getSmartProcessItem($riskLevelsEntityTypeId));
    }

    #[TestDox('Test filterContactsWithRiskLevel')]
    public function testFilterContactsWithRiskLevel(): void
    {
        // Load entity type ID and risk levels for the current portal
        $riskLevelsEntityTypeId = $this->riskLevelMapper->getEntityTypeId($this->sb);
        $riskLevelQueries = new RiskLevelQueries($this->logger);
        $indexedRiskLevels = $riskLevelQueries->getRiskLevels($this->sb, $riskLevelsEntityTypeId);

        // Test with LOW risk level
        $testRiskLevel = RiskLevel::LOW;
        $beforeCnt = $this->scoreQueries->filterContactsWithRiskLevel(
            $this->sb,
            $testRiskLevel
        )->getCoreResponse()->getResponseData()->getPagination()->getTotal();

        // Create a contact without risk level
        $xmlId = Uuid::v7()->toRfc4122();
        $contactResult = $this->sb->getCRMScope()->contact()->add([
            'NAME' => sprintf('test contact with risk level %s', time()),
            'ORIGIN_ID' => $xmlId
        ]);
        $contactId = $contactResult->getId();

        // Assign LOW risk level to the contact
        $this->sb->getCRMScope()->contact()->update(
            $contactId,
            [
                'PARENT_ID_' . $riskLevelsEntityTypeId => $indexedRiskLevels[$testRiskLevel->value]->b24EntityId,
            ]
        );

        // Verify count increased by 1
        $afterCnt = $this->scoreQueries->filterContactsWithRiskLevel(
            $this->sb,
            $testRiskLevel
        )->getCoreResponse()->getResponseData()->getPagination()->getTotal();
        $this->assertEquals($beforeCnt + 1, $afterCnt);

        // Query for the specific contact with the filter
        $contact = $this->scoreQueries->filterContactsWithRiskLevel(
            $this->sb,
            $testRiskLevel,
            [
                'ORIGIN_ID' => $xmlId
            ]
        )->getContacts()[0];

        // Verify the contact has the correct risk level
        $this->assertEquals(
            $indexedRiskLevels[$testRiskLevel->value]->b24EntityId,
            $contact->getSmartProcessItem($riskLevelsEntityTypeId)
        );
    }

    public function setUp(): void
    {
        $this->logger = LoggerFactory::create();
        $this->riskLevelMapper = new RiskLevelMapper($this->logger);
        $this->scoreQueries = new ScoreQueries(
            new RiskLevelQueries($this->logger),
            $this->riskLevelMapper,
            $this->logger
        );

        // connect to TEST bitrix24 portal
        $this->sb = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_INCOMING_WEBHOOK_URL_TEST'],
            null,
            $this->logger
        );

        // create risk levels
        $riskLevelInstaller = new RiskLevelInstaller(LoggerFactory::create());
        $riskLevelInstaller->install($this->sb);

        // create score field
        $scoreInstaller = new ScoreInstaller(
            new ScoreFieldMapper(),
            $this->logger
        );
        $scoreInstaller->install($this->sb);
    }
}
