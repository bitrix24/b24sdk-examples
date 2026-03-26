<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Workflow\Activities;

/**
 * DTO for a store activity result
 */
readonly class ActivityResponse
{
    public function __construct(
        public string $eventToken,
        public array $payload,
        public ?string $logMessage
    ) {
    }
}