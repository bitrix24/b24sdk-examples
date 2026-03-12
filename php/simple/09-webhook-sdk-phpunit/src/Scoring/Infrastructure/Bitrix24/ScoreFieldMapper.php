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

namespace App\Scoring\Infrastructure\Bitrix24;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Psr\Log\LoggerInterface;

class ScoreFieldMapper
{
    public function getName(): string
    {
        return 'APP_SCORE';
    }

    public function getXmlId(): string
    {
        return 'ACME_INC_APP_SCORE';
    }
}
