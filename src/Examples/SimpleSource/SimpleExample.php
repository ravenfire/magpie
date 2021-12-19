<?php

namespace Ravenfire\Magpie\Examples\SimpleSource;

use Ravenfire\Magpie\Sources\AbstractSource;

class SimpleExample extends AbstractSource
{
    static public function getKey(): string
    {
        return 'example-source';
    }

    public function getDefaultLoggingContext(): array
    {
        return [
            'example' => ['stuff', 'here']
        ];
    }

    public function execute()
    {
        $this->info("Michael is cool", ['b' => true]);
    }
}