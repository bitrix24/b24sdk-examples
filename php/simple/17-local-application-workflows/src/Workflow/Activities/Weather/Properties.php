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

namespace App\Workflow\Activities\Weather;

use InvalidArgumentException;

final readonly class Properties
{
    /**
     * @param array $prop
     * @return self
     */
    public static function initFromArray(array $prop): self
    {
        return new self($prop['country'], $prop['city']);
    }

    /**
     * @param non-empty-string $country
     * @param non-empty-string $city
     */
    public function __construct(
        public string $country,
        public string $city,
    ) {
        if ($this->country === '') {
            throw new InvalidArgumentException('Country property field is required');
        }
        if ($this->city === '') {
            throw new InvalidArgumentException('City property field is required');
        }
    }

    // todo add interface
    // todo add checks for constructor?
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