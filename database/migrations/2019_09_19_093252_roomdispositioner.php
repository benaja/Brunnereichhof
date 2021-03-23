<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Roomdispositioner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->integer('number');
            $table->string('location', 100)->nullable();
            $table->string('comment', 500)->nullable();
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });

        Schema::create('bed', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('width', 100)->nullable();
            $table->smallInteger('places');
            $table->string('comment', 500)->nullable();
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });

        Schema::create('inventar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->double('price');
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });

        Schema::create('bed_inventar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bed_id');
            $table->unsignedBigInteger('inventar_id');
            $table->integer('amount');
            $table->integer('amount_2');

            $table->foreign('bed_id')->references('id')->on('bed');
            $table->foreign('inventar_id')->references('id')->on('inventar');
        });

        Schema::create('bed_room', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('bed_id')->nullable();

            $table->foreign('room_id')->references('id')->on('room');
            $table->foreign('bed_id')->references('id')->on('bed');
        });

        Schema::create('reservation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bed_room_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('entry');
            $table->date('exit');
            $table->timestamps();

            $table->foreign('bed_room_id')->references('id')->on('bed_room');
            $table->foreign('employee_id')->references('id')->on('employee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservation');
        Schema::dropIfExists('bed_room');
        Schema::dropIfExists('bed_inventar');
        Schema::dropIfExists('inventar');
        Schema::dropIfExists('bed');
        Schema::dropIfExists('room');
    }
}
