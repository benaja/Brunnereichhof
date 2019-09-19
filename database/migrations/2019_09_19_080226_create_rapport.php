<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRapport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });

        Schema::create('rapport', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->boolean('isFinished');
            $table->date('startdate');
            $table->string('comment_mo')->nullable();
            $table->string('comment_tu')->nullable();
            $table->string('comment_we')->nullable();
            $table->string('comment_th')->nullable();
            $table->string('comment_fr')->nullable();
            $table->string('comment_sa')->nullable();
            $table->unsignedBigInteger('default_project_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customer');
            $table->foreign('default_project_id')->references('id')->on('project');
        });

        Schema::create('foodtype', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('foodname');
            $table->timestamps();
        });

        Schema::create('rapportdetail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rapport_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('foodtype_id');
            $table->double('hours');
            $table->smallInteger('day');
            $table->string('comment')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->foreign('rapport_id')->references('id')->on('rapport');
            $table->foreign('project_id')->references('id')->on('project');
            $table->foreign('employee_id')->references('id')->on('employee');
            $table->foreign('foodtype_id')->references('id')->on('foodtype');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rapportdetail');
        Schema::dropIfExists('foodtype');
        Schema::dropIfExists('rapport');
        Schema::dropIfExists('project');
    }
}