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

namespace App\Workflow\Activities;

final readonly class ActivityHandlerMetadata
{
    public function __construct(
        /**
         * Внутренний идентификатор активити. Является уникальным в рамках приложения.
         * Допустимые символы — a-z, A-Z, 0-9, точка, дефис и нижнее подчеркивание _
         * @var non-empty-string
         */
        private string $activityCode
    ) {
    }

    /**
     * @param non-empty-string $activityCode
     * @return bool
     */
    public function isCanProcess(string $activityCode): bool
    {
        return $this->activityCode === $activityCode;
    }
}

