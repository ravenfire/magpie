<?php

namespace Ravenfire\Magpie\Data\Audit;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

/**
 * Details table information for the Audit table.
 */
class AuditTableMigration extends AbstractMigration
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
            $table->integer('job_id');
            $table->integer('record_id');
            $table->string('source_key');
            $table->string('column_name')->nullable(true);
            $table->string('old_value')->nullable(true);
            $table->string('new_value')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Sets table name to "audit".
     *
     * @return string
     */
    static public function getTableName(): string
    {
        return 'audit';
    }
}

// 1. Job will have MANY audit records
// 2. Audit records will belong to ONE Job

// 3. Audit Record will have ONE source record
// 4. Source Records will belong to ONE audit record

// 5. Job will have MANY logs
// 6. Logs will have ONE job