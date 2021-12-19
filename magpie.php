<?php

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Ravenfire\Magpie\Config;
use Ravenfire\Magpie\Magpie;
use Ravenfire\Magpie\Sources\SimpleExample\SimpleExample;

require 'vendor/autoload.php';

// @todo: test a sources (data-example) install and uninstall
// @todo: Man application needs to define the "primary entity" table (game)
// @todo: Create eloquent models for primary entity and sources
// @todo: clean up CLI commands
// @todo: Config and Logging to correct places

$config = new Config(); // @todo: Config::from($json/yml/dotenv)

// Create the logger
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
$magpie->setLogger($logger);

try {
    $magpie->addSource(SimpleExample::class);
} catch (Exception $exception) {
    die($exception->getMessage()); // @todo: Probably better handling
}

$magpie->run();