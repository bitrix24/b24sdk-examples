<?php

namespace App\Tests\Unit\Scoring;

use App\Scoring\RiskLevel;
use App\Scoring\Score;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Score::class)]
class ScoreTest extends TestCase
{
    #[TestDox('Test Score constructor accepts valid scores and matching risk levels')]
    #[DataProvider('validScoresProvider')]
    public function testConstructorAcceptsValidData(int $scores, RiskLevel $risk): void
    {
        $score = new Score($scores, $risk);

        $this->assertSame($scores, $score->scores);
        $this->assertSame($risk, $score->risk);
    }

    #[TestDox('Test Score constructor throws exception when scores is out of range (1-20)')]
    #[DataProvider('invalidRangeProvider')]
    public function testConstructorThrowsExceptionForInvalidRange(int $invalidScores): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores must be between 1 and 20');

        new Score($invalidScores, RiskLevel::LOW);
    }

    #[TestDox('Test Score constructor throws exception when risk level does not match scores')]
    #[DataProvider('mismatchedRiskProvider')]
    public function testConstructorThrowsExceptionForMismatchedRisk(int $scores, RiskLevel $wrongRisk): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Risk level .* does not match score/');

        new Score($scores, $wrongRisk);
    }

    #[TestDox('Test Score::fromScores() factory method creates valid Score objects')]
    #[DataProvider('factoryMethodProvider')]
    public function testFromScoresFactoryMethod(int $scores, RiskLevel $expectedRisk): void
    {
        $score = Score::fromScores($scores);

        $this->assertSame($scores, $score->scores);
        $this->assertSame($expectedRisk, $score->risk);
    }

    #[TestDox('Test Score::fromScores() throws exception for invalid range')]
    public function testFromScoresThrowsExceptionForInvalidRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scores must be between 1 and 20');

        Score::fromScores(0);
    }

    /**
     * @return array<string, array{scores: int, risk: RiskLevel}>
     */
    public static function validScoresProvider(): array
    {
        return [
            'low risk minimum' => ['scores' => 1, 'risk' => RiskLevel::LOW],
            'low risk boundary' => ['scores' => 5, 'risk' => RiskLevel::LOW],
            'medium risk minimum' => ['scores' => 6, 'risk' => RiskLevel::MEDIUM],
            'medium risk boundary' => ['scores' => 9, 'risk' => RiskLevel::MEDIUM],
            'high risk minimum' => ['scores' => 10, 'risk' => RiskLevel::HIGH],
            'high risk maximum' => ['scores' => 20, 'risk' => RiskLevel::HIGH],
        ];
    }

    /**
     * @return array<string, array{invalidScores: int}>
     */
    public static function invalidRangeProvider(): array
    {
        return [
            'zero' => ['invalidScores' => 0],
            'negative' => ['invalidScores' => -5],
            'above maximum' => ['invalidScores' => 21],
            'far above maximum' => ['invalidScores' => 100],
        ];
    }

    /**
     * @return array<string, array{scores: int, wrongRisk: RiskLevel}>
     */
    public static function mismatchedRiskProvider(): array
    {
        return [
            'low score with medium risk' => ['scores' => 3, 'wrongRisk' => RiskLevel::MEDIUM],
            'low score with high risk' => ['scores' => 5, 'wrongRisk' => RiskLevel::HIGH],
            'medium score with low risk' => ['scores' => 7, 'wrongRisk' => RiskLevel::LOW],
            'medium score with high risk' => ['scores' => 9, 'wrongRisk' => RiskLevel::HIGH],
            'high score with low risk' => ['scores' => 15, 'wrongRisk' => RiskLevel::LOW],
            'high score with medium risk' => ['scores' => 20, 'wrongRisk' => RiskLevel::MEDIUM],
        ];
    }

    /**
     * @return array<string, array{scores: int, expectedRisk: RiskLevel}>
     */
    public static function factoryMethodProvider(): array
    {
        return [
            'low risk score' => ['scores' => 3, 'expectedRisk' => RiskLevel::LOW],
            'medium risk score' => ['scores' => 7, 'expectedRisk' => RiskLevel::MEDIUM],
            'high risk score' => ['scores' => 15, 'expectedRisk' => RiskLevel::HIGH],
            'boundary low-medium' => ['scores' => 6, 'expectedRisk' => RiskLevel::MEDIUM],
            'boundary medium-high' => ['scores' => 10, 'expectedRisk' => RiskLevel::HIGH],
        ];
    }
}
