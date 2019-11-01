<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHourrecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hourrecords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('culture_id')->nullable();
            $table->smallInteger('week');
            $table->smallInteger('year');
            $table->double('hours')->nullable();
            $table->string('comment')->nullable();
            $table->boolean('createdByAdmin')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customer');
            $table->foreign('culture_id')->references('id')->on('culture');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hourrecords');
    }
}
