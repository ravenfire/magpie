<?php

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Ravenfire\Magpie\Config;
use Ravenfire\Magpie\Examples\DataSource\DataExample;
use Ravenfire\Magpie\Examples\PrimaryEntity\ExamplePrimaryEntity;
use Ravenfire\Magpie\Examples\SimpleSource\SimpleExample;
use Ravenfire\Magpie\Magpie;

require 'vendor/autoload.php';

// @todo: Main application needs to define the "primary entity" table (game)
// @todo: look at what needs to be static
// @todo: Create eloquent models for primary entity and sources
// @todo: clean up CLI commands
// @todo: Config and Logging to correct places
// @todo: WISH - add tags

$config = new Config(); // @todo: Config::from($json/yml/dotenv)

// Create the logger
// @todo: all of this can move into `run()` or `__construct()`
$logger = new Logger('magpie');
//$logger->pushHandler(new MagpieDataLogger());
$logger->pushHandler(new class implements HandlerInterface {

    public function isHandling(array $record): bool
    {
        return true;
    }

    public function handle(array $record): bool
    {
        return true;
    }

    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function close(): void
    {
//         TODO: Implement close() method.
    }
});

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