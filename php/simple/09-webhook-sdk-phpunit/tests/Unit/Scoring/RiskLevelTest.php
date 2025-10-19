<?php

namespace App\Tests\Unit\Scoring;

use App\Scoring\RiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RiskLevel::class)]
class RiskLevelTest extends TestCase
{
    #[TestDox('Test RiskLevel::fromScores() correctly determines LOW risk for scores 1-5')]
    #[DataProvider('lowRiskScoresProvider')]
    public function testFromScoresReturnsLowRisk(int $scores): void
    {
        $result = RiskLevel::fromScores($scores);

        $this->assertSame(RiskLevel::LOW, $result);
    }

    #[TestDox('Test RiskLevel::fromScores() correctly determines MEDIUM risk for scores 6-9')]
    #[DataProvider('mediumRiskScoresProvider')]
    public function testFromScoresReturnsMediumRisk(int $scores): void
    {
        $result = RiskLevel::fromScores($scores);

        $this->assertSame(RiskLevel::MEDIUM, $result);
    }

    #[TestDox('Test RiskLevel::fromScores() correctly determines HIGH risk for scores 10-20')]
    #[DataProvider('highRiskScoresProvider')]
    public function testFromScoresReturnsHighRisk(int $scores): void
    {
        $result = RiskLevel::fromScores($scores);

        $this->assertSame(RiskLevel::HIGH, $result);
    }

    /**
     * @return array<string, array{scores: int}>
     */
    public static function lowRiskScoresProvider(): array
    {
        return [
            'minimum score' => ['scores' => 1],
            'middle low score' => ['scores' => 3],
            'boundary low score' => ['scores' => 5],
        ];
    }

    /**
     * @return array<string, array{scores: int}>
     */
    public static function mediumRiskScoresProvider(): array
    {
        return [
            'boundary low-medium score' => ['scores' => 6],
            'middle medium score' => ['scores' => 7],
            'boundary medium-high score' => ['scores' => 9],
        ];
    }

    /**
     * @return array<string, array{scores: int}>
     */
    public static function highRiskScoresProvider(): array
    {
        return [
            'boundary medium-high score' => ['scores' => 10],
            'middle high score' => ['scores' => 15],
            'maximum score' => ['scores' => 20],
        ];
    }
}
