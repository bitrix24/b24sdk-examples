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

namespace App\Services;

use App\Scoring\DTO\Risk;
use App\Scoring\DTO\Score;
use Psr\Log\LoggerInterface;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;
use Random\RandomException;


class DefaultScoringModel implements ScoringInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws RandomException
     */
    public function score(ContactItemResult $b24ContactItem): Score
    {
        $this->logger->debug('ScoringModel.score.start', ['b24ContactId' => $b24ContactItem->ID]);
        
        // complex scoring logic start
        if ($b24ContactItem->ID % 2 === 1) {
            $scores = random_int(11, 20);
        }else{
            $scores = random_int(1, 10);
        }

        if ($scores > 10) {
            $risk = Risk::HIGH;
        } elseif ($scores >= 5) {
            $risk = Risk::MEDIUM;
        } else {
            $risk = Risk::LOW;
        }
        // complex scoring logic end

        $this->logger->debug('ScoringModel.score.finish', ['risk' => $risk->value]);
        return new Score($scores, $risk);
    }
}