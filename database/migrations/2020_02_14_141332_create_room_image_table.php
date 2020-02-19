<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_image', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('path', 200);
            $table->integer('room_id');
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('room');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_image');
    }
}
