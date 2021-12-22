<?php

namespace Ravenfire\Magpie\Data\Logs;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class LogsTableMigration extends AbstractMigration
{
    static public function getTableName(): string
    {
        return 'logs';
    }

    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('source_key');
            $table->string('job_id');
            $table->integer('level');
            $table->string('level_name');
            $table->string('channel');
            $table->dateTime('datetime');
            $table->string('message');
            $table->json('context');
            $table->timestamps();
        });
    }
}