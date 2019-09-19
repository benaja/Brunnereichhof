<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemerecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timerecord', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->boolean('lunch');
            $table->boolean('breakfast');
            $table->boolean('dinner');
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::create('worktype', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('name_de', 200);
            $table->string('color', 100);
            $table->string('short_name', 10);
        });

        Schema::create('hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timerecord_id');
            $table->time('from');
            $table->time('to');
            $table->unsignedBigInteger('worktype_id');
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->foreign('timerecord_id')->references('id')->on('timerecord');
            $table->foreign('worktype_id')->references('id')->on('worktype');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hours');
        Schema::dropIfExists('worktype');
        Schema::dropIfExists('timerecord');
    }
}
