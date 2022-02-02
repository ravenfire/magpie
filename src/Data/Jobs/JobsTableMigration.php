<?php

namespace Ravenfire\Magpie\Data\Jobs;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

/**
 * Details table information for the Jobs table.
 */
class JobsTableMigration extends AbstractMigration
{
    /**
     * Set column names.
     *
     * @return void
     */
    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('extra')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Sets table name to "jobs".
     *
     * @return string
     */
    static public function getTableName(): string
    {
        return 'jobs';
    }
}