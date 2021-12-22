<?php

use Monolog\Logger;
use Ravenfire\Magpie\Config;
use Ravenfire\Magpie\Examples\DataSource\DataExample;
use Ravenfire\Magpie\Examples\PrimaryEntity\ExamplePrimaryEntity;
use Ravenfire\Magpie\Examples\SimpleSource\SimpleExample;
use Ravenfire\Magpie\Logging\DatabaseLogger;
use Ravenfire\Magpie\Magpie;

require 'vendor/autoload.php';

// @todo: The logs as well
// @todo: Create eloquent models for primary entity and sources
// @todo: Connect to (and ensure exists) the primary entity
// @todo: clean up CLI commands
// @todo: Config and Logging to correct places
// @todo: WISH - add tags

$config = new Config(); // @todo: Config::from($json/yml/dotenv)

// Create the logger
// @todo: all of this can move into `run()` or `__construct()`
$logger = new Logger('magpie');
//$logger->pushHandler(new MagpieDataLogger());
$logger->pushHandler(new DatabaseLogger());

$magpie = new Magpie($config);
$magpie->setLogger($logger); // @todo: make optional
$magpie->setPrimaryEntity(ExamplePrimaryEntity::class);

try {
    $magpie->addSource(SimpleExample::class);
    $magpie->addSource(DataExample::class);
} catch (Exception $exception) {
    die($exception->getMessage()); // @todo: Probably better handling
}

$magpie->run();