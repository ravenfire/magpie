<?php

namespace Ravenfire\Magpie\Examples\DataSource;

use Ravenfire\Magpie\Sources\AbstractSource;

class DataExample extends AbstractSource
{
    static public function getMigrations(): array
    {
        return [
            TableMigration::class,
        ];
    }

    static public function getKey(): string
    {
        return 'data-example';
    }

    public function execute()
    {
        $model = new DataModel();
        $model->name = 'Michael';
        $model->favorite_color = 'Purple';

        $this->save($model);

        $model = new DataModel();
        $model->name = 'James';
        $model->favorite_color = 'Orange';

        $this->save($model);
    }
}