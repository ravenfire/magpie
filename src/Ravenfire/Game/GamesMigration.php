<?php

namespace Ravenfire\Magpie\Ravenfire\Game;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

/**
 * Details table information for the GameModel.
 */
class GamesMigration extends AbstractMigration
{
    /**
     * Sets column names.
     *
     * @return void
     */
    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('game_key')->unique();
            $table->string('name');
            $table->date('year_published');
            $table->string('description')->nullable(true);
            $table->string('boardgame_publisher');
            $table->string('boardgame_artist')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Sets table name to "games".
     *
     * @return string
     */
    static public function getTableName(): string
    {
        return "games";
    }
}