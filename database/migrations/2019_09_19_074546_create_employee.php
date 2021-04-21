<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('callname')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('nationality')->nullable();
            $table->boolean('isDriver')->nullable();
            $table->boolean('german_knowledge')->nullable();
            $table->boolean('english_knowledge')->nullable();
            $table->string('sex', 50);
            $table->string('comment')->nullable();
            $table->string('experience')->nullable();
            $table->boolean('isActive')->nullable();
            $table->boolean('isGuest')->nullable();
            $table->boolean('isDeleted')->default(false);
            $table->boolean('isIntern')->nullable();
            $table->string('profileimage')->nullable();
            $table->string('allergy')->nullable();
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
        Schema::dropIfExists('employee');
    }
}
