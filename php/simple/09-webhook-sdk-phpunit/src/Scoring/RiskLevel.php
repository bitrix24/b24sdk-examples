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

enum RiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    /**
     * Determine risk level from numeric score based on business rules:
     * - LOW: 1-5
     * - MEDIUM: 6-9
     * - HIGH: 10-20
     *
     * @param positive-int $scores Score value (1-20)
     */
    public static function fromScores(int $scores): self
    {
        return match (true) {
            $scores <= 5 => self::LOW,
            $scores <= 9 => self::MEDIUM,
            default => self::HIGH,
        };
    }
}
