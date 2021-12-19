<?php

namespace Ravenfire\Magpie\Examples\DataSource;

use Ravenfire\Magpie\Sources\AbstractSource;

class DataExample extends AbstractSource
{
    static public function getMigrations(): array
    {
        return [
            'data_example' => TableMigration::class,
        ];
    }

    static public function getKey(): string
    {
        return 'data-example';
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }
}