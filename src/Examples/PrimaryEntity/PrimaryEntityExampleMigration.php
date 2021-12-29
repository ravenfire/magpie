<?php

namespace Ravenfire\Magpie\Examples\PrimaryEntity;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class PrimaryEntityExampleMigration extends AbstractMigration //Michael was PrimaryMigration
{
    static public function getTableName(): string
    {
        return 'primary_entity';
    }

    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('one');
            $table->string('two');
            $table->string('three');
            $table->timestamps();
        });
    }
}