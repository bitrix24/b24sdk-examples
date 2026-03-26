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

namespace App\Scoring\Infrastructure\Bitrix24\RiskLevels;

use App\Scoring\RiskLevel;

final readonly class SmartProcessItemRiskLevel
{
    public function __construct(
        public RiskLevel $risk,
        public int $b24EntityId,
        public int $b24EntityTypeId,
    ) {
    }
}
