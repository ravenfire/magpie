<?php

namespace Ravenfire\Magpie\Ravenfire;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class GamesMigration extends AbstractMigration
{

    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('year_published');
            $table->string('description');
            $table->string('boardgame_publisher');
            $table->string('boardgame_artist')->nullable(true);
            $table->timestamps();
        });
    }

    static public function getTableName(): string
    {
        return "games";
    }
}