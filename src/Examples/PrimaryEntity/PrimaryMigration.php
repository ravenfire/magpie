<?php

namespace Ravenfire\Magpie\Examples\PrimaryEntity;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class PrimaryMigration extends AbstractMigration
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