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

namespace App\Robots;

use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;

final readonly class RobotHandlerMetadata
{
    public function __construct(
        /**
         * Внутренний идентификатор робота. Является уникальным в рамках приложения.
         * Допустимые символы — a-z, A-Z, 0-9, точка, дефис и нижнее подчеркивание _
         * @var non-empty-string
         */
        public string $robotCode,
        /**
         * @var class-string
         */
        public string $handlerClassName
    ) {
    }
}

