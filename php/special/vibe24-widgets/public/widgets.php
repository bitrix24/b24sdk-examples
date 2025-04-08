<?php

declare(strict_types=1);

namespace App;

require_once dirname(__DIR__). '/vendor/autoload.php';

Application::createWidgets('widgets');

// Turn on the debug mode for our widgets
Application::getB24Service()
    ->core->call(
        'landing.repowidget.debug',
        ['enable' => true]
    )->getResponseData()->getResult();

