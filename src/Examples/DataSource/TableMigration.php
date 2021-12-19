<?php

namespace Ravenfire\Magpie\Examples\DataSource;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class TableMigration extends Migration
{
    protected $table_name = 'data_example';

    public function up()
    {
        Manager::schema()->create($this->table_name, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('favorite_color');
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