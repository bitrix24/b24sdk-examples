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
        if ($scores < 0 || $scores > 20) {
            throw new InvalidArgumentException('Scores must be between 0 and 20');
        }
    }
}