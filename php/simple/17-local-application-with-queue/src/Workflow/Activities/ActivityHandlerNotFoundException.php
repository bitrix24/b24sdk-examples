<?php

declare(strict_types=1);

namespace App\Workflow\Activities;

final class ActivityHandlerNotFoundException extends \RuntimeException
{
    public function __construct(string $activityCode)
    {
        parent::__construct(sprintf('Activity handler for code "%s" was not found.', $activityCode));
    }
}
