<?php

namespace Ravenfire\Magpie\Data\Jobs;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class JobsTableMigration extends AbstractMigration
{
    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('extra')->nullable(true);
            $table->timestamps();
        });
    }

    static public function getTableName(): string
    {
        return 'jobs';
    }
}