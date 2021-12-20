<?php

namespace Ravenfire\Magpie\Examples\PrimaryEntity;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PrimaryMigration extends Migration
{
    public $table_name = 'primary_entity';

    public function up()
    {
        Manager::schema()->create($this->table_name, function (Blueprint $table) {
            $table->id();
            $table->string('one');
            $table->string('two');
            $table->string('three');
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
        Manager::schema()->drop($this->table_name);
    }
}