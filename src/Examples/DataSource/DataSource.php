<?php

namespace Ravenfire\Magpie\Examples\DataSource;

use Ravenfire\Magpie\Examples\PrimaryEntity\PrimaryEntityExample;
use Ravenfire\Magpie\Sources\AbstractSource;

class DataSource extends AbstractSource
{
    static public function getMigrations(): array
    {
        return [
            DataExampleMigration::class,
        ];
    }

    static public function getKey(): string
    {
        return 'data-example';
    }

    public function execute()
    {
        $primary = PrimaryEntityExample::where('id', 1);

        $model = new DataExample();
        $model->name = 'Michael';
        $model->favorite_color = 'Purple';

        $this->save($model, $primary);

        $model = new DataExample();
        $model->name = 'James';
        $model->favorite_color = 'Orange';

        $this->save($model, $primary);
    }
}