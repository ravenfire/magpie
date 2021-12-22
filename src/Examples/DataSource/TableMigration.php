<?php

namespace Ravenfire\Magpie\Examples\DataSource;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class TableMigration extends AbstractMigration
{
    static public function getTableName(): string
    {
        return 'data_example';
    }

    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('favorite_color');
            $table->timestamps();
        });
    }
}