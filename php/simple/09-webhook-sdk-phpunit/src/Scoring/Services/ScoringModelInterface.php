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

namespace App\Scoring\Services;

use App\Scoring\Score;

interface ScoringModelInterface
{
    /**
     * @param array<string, mixed> $personMetadata
     */
    public function scorePerson(array $personMetadata): Score;
}
