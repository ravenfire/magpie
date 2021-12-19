<?php

namespace Ravenfire\Magpie\Data\Migrations;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogsTable extends Migration
{
    public function up()
    {
        Manager::schema()->create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_key');
            $table->integer('level');
            $table->string('level_name');
            $table->string('channel');
            $table->dateTime('datetime');
            $table->string('message');
            $table->json('context');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Manager::schema()->drop('logs');
    }
}