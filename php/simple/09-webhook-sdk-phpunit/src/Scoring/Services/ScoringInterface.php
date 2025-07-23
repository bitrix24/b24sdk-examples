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

use App\Scoring\DTO\Score;
use Bitrix24\SDK\Services\CRM\Contact\Result\ContactItemResult;

interface ScoringInterface
{
    public function score(ContactItemResult $b24ContactItem): Score;
}