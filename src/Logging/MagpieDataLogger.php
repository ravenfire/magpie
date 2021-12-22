<?php

namespace Ravenfire\Magpie\Logging;

use Monolog\Handler\HandlerInterface;
use Ravenfire\Magpie\Data\Logs\Log;

// @todo: needs to be OH SO MUCH BETTER
class MagpieDataLogger implements HandlerInterface
{
    public function isHandling(array $record): bool
    {
        if (!array_key_exists('context', $record) or count($record['context']) === 0) {
            return false;
        }

        return true;
    }

    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function handle(array $record): bool
    {
        // @todo: Needs a better way to know if its in install mode
        if (count($record['context']) === 0) {
            return false;
        }

        $log = new Log();
        $log->source_key = $record['context']['source']['key'] ?? 'magpie';
        $log->job_id = $record['context']['source']['job_id'] ?? null;
        $log->level = $record['level'];
        $log->level_name = $record['level_name'];
        $log->channel = $record['channel'];
        $log->datetime = $record['datetime'];
        $log->message = $record['message'];
        $log->context = json_encode($record['context']);
        $log->save();

        return true;
    }

    public function close(): void
    {
//         TODO: Implement close() method.
    }
}