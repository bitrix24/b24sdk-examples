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

use App\Scoring\RiskLevel;
use App\Scoring\Score;
use Psr\Log\LoggerInterface;
use Random\RandomException;


readonly class DefaultScoringModel implements ScoringModelInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws RandomException
     */
    public function scorePerson(array $personMetadata): Score
    {
        $this->logger->debug('ScoringModel.score.start', ['meta' => $personMetadata]);

        // complex scoring logic start
        $fullName = $personMetadata['NAME'] . ' ' . $personMetadata['LAST_NAME'] . ' ' . $personMetadata['SECOND_NAME'];
        if (strlen($fullName) % 2 === 1) {
            $scores = random_int(11, 20);
        } else {
            $scores = random_int(1, 10);
        }
        // complex scoring logic end

        $this->logger->debug('ScoringModel.score.finish', ['scores' => $scores]);
        return Score::fromScores($scores);
    }
}