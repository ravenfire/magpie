<?php

namespace Ravenfire\Magpie\Ravenfire;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Ravenfire\Magpie\Data\Migrations\AbstractMigration;

class BoardGameGeekMigration extends AbstractMigration
{

    public function up()
    {
        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->integer('game_id');
            $table->foreign('game_id')->references('id')->on('games');
            $table->integer('bgg_foreign_id');
            $table->integer('number_of_players');
            $table->integer('for_player_ages');
            $table->integer('average_playtime');
            $table->string('boardgame_mechanic');
//            $table->('images');
//            $table->('thumbnail');
//            $table->string('boardgame_family');
            $table->string('boardgame_designer');
            $table->string('boardgame_version');
            $table->string('boardgame_implementation');
//            $table->string('poll');
            $table->string('comments');
//            $table->string('statistics');
            $table->timestamps();
        });
    }

    static public function getTableName(): string
    {
        return "board_game_geek";
    }
}