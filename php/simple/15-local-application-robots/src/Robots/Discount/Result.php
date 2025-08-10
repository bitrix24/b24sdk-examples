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

namespace App\Robots\Discount;

final readonly class Result
{
    public function __construct(
        public string $amount,
        public string $comment
    ) {
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'comment' => $this->comment
        ];
    }

    // todo навесить интерфейс?
    public static function getMetadata(): array
    {
        return [
            'amount' => [
                'name' => [
                    'ru' => 'Сумма скидки'
                ],
                'type' => 'string',
            ],
            'comment' => [
                'name' => [
                    'ru' => 'Комментарий'
                ],
                'type' => 'string',
            ]
        ];
    }
}