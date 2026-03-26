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
use Psr\Log\LoggerInterface;

readonly class ScoringProcessor
{
    public function __construct(
        private ScoringModelInterface $model,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param array<string, mixed> $personMetadata
     */
    public function scorePerson(array $personMetadata): Score
    {
        $this->logger->debug('ScoringProcessor.scorePerson.start', ['metadata' => $personMetadata]);

        // call scoring model
        $score = $this->model->scorePerson($personMetadata);

        $this->logger->debug('ScoringProcessor.scorePerson.finish', ['score' => $score->scores, 'risk' => $score->risk->value]);
        return $score;
    }
}
