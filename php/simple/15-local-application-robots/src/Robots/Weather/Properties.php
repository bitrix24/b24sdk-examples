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

namespace App\Robots\Weather;

final readonly class Properties
{
    public function __construct(
        public string $country,
        public string $city,
    ) {
    }
    // todo навесить интерфейс?
    public static function getMetadata(): array
    {
        return [
            'city' => [
                'name' => [
                    'ru' => 'Город'
                ],
                'type' => 'string',
            ],
            'country' => [
                'name' => [
                    'ru' => 'Страна'
                ],
                'type' => 'string',
            ]
        ];
    }
}