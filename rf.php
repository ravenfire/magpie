<?php

use Monolog\Logger;
use Ravenfire\Magpie\Config;
use Ravenfire\Magpie\Logging\MagpieDataLogger;
use Ravenfire\Magpie\Magpie;
use Ravenfire\Magpie\Ravenfire\BoardGameGeek\BoardGameGeekSource;
use Ravenfire\Magpie\Ravenfire\Game\GamesPrimaryEntity;

require 'vendor/autoload.php';

// @todo: Create eloquent models for primary entity
// @todo: Connect to (and ensure exists) the primary entity

$config = new Config(); // @todo: Config::from($json/yml/dotenv)

// Create the logger
// @todo: all of this can move into `run()` or `__construct()`
$logger = new Logger('magpie'); //@todo: move to magpie constructor
//$logger->pushHandler(new MagpieDataLogger());
$logger->pushHandler(new MagpieDataLogger());

$magpie = new Magpie($config);
$magpie->setLogger($logger); // @todo: make optional
$magpie->setPrimaryEntity(GamesPrimaryEntity::class);

try {
    $magpie->addSource(BoardGameGeekSource::class);
} catch (Exception $exception) {
    die($exception->getMessage()); // @todo: Probably better handling
}

$magpie->run();