<?php

namespace Ravenfire\Magpie\Sources\DataExample;

use Ravenfire\Magpie\Sources\AbstractSource;

class DataExample extends AbstractSource
{
    static public function getKey(): string
    {
        return 'data-example';
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }
}