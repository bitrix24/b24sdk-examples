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

use Psr\Log\LoggerInterface;

final readonly class Gamma
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function processGamma(): void
    {
        $this->logger->info("Process Gamma.start", [
            'paramX' => 22,
            'paramY' => 33,
        ]);
        sleep(1);

        $this->logger->notice('prepare to call unknown function, hold on');
        // call unknown function
        $funcName = '\App\Services\fooooo';
        $funcName(1, __FILE__, time());
    }
}