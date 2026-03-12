<?php

/**
 * This file is part of the b24sdk examples package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Scoring;

use InvalidArgumentException;

final readonly class Score
{
    public function __construct(
        /**
         * @param positive-int $scores
         */
        public int $scores,
        public RiskLevel $risk
    ) {
        if ($scores <= 0 || $scores > 20) {
            throw new InvalidArgumentException('Scores must be between 1 and 20');
        }

        // Validate that risk level matches the score according to business rules
        $riskLevel = RiskLevel::fromScores($scores);
        if ($risk !== $riskLevel) {
            throw new InvalidArgumentException(
                sprintf(
                    'Risk level %s does not match score %d (expected %s)',
                    $risk->value,
                    $scores,
                    $riskLevel->value
                )
            );
        }
    }

    /**
     * Factory method to create Score from numeric value.
     * Automatically determines the appropriate RiskLevel.
     *
     * @param positive-int $scores Score value (1-20)
     * @throws InvalidArgumentException
     */
    public static function fromScores(int $scores): self
    {
        return new self($scores, RiskLevel::fromScores($scores));
    }
}
