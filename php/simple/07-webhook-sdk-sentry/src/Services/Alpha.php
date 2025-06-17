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

final readonly class Alpha
{
    public function __construct(
        private Beta $beta,
        private Gamma $gamma,
        private LoggerInterface $logger,
    ) {
    }

    public function run(): void
    {
        $this->logger->info("Alpha.run.start",[
            'param1' => 'value1',
            'param2' => 'value2',
        ]);
        sleep(1);
        $this->beta->processBeta();
        $this->gamma->processGamma();
        $this->logger->info("Alpha.run.finish");
    }

    public static function init(LoggerInterface $logger): self
    {
        return new self(
            new Beta($logger),
            new Gamma($logger),
            $logger
        );
    }
}