<?php

namespace Ravenfire\Magpie\Data\Migrations;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;

/**
 *
 */
abstract class AbstractMigration extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Manager::schema()->drop(static::getTableName());
    }

    abstract public function up();

    abstract static public function getTableName(): string;
}