<?php

declare(strict_types=1);

use JR\Tracker\Command\FixtureLoaderCommand;
use JR\Tracker\Command\GenerateAppKeyCommand;

return [
    GenerateAppKeyCommand::class,
    FixtureLoaderCommand::class,
];