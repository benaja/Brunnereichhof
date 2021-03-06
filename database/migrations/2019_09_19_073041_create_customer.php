<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('addition')->nullable();
            $table->string('place');
            $table->string('street');
            $table->string('plz');
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('hasCatering')->nullable();
            $table->string('kitchen_infrastructure')->nullable();
            $table->integer('max_catering')->nullable();
            $table->string('comment_catering')->nullable();
            $table->string('driver_info')->nullable();
            $table->string('comment')->nullable();
            $table->string('maps')->nullable();
            $table->string('secret')->nullable();
            $table->integer('customer_number')->nullable();
            $table->boolean('needs_payment_order')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer');
    }
}
