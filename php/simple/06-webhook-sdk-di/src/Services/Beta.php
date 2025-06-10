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

final readonly class Beta
{
    public function __construct(
        private LoggerInterface $logger,
    ){
    }

    public function processBeta():void
    {
        $this->logger->info("Process Beta");
    }
}