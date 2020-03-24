<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hours', function (Blueprint $table) {
            $table->dropForeign('hours_ibfk_2');
        });

        Schema::table('worktype', function (Blueprint $table) {
            $table->boolean('manually')->default(true);
            $table->bigIncrements('id')->change();
        });

        Schema::table('hours', function (Blueprint $table) {
            $table->unsignedBigInteger('worktype_id')->nullable()->change();
            $table->foreign('timerecord_id')->references('id')->on('timerecord');
        });

        Schema::create('work_input_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('worktype_id')->nullable();
            $table->string('name');
            $table->double('hours');

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
        Schema::table('worktype', function (Blueprint $table) {
            $table->dropColumn('manually');
        });
        Schema::dropIfExists('work_input_type');
    }
}
