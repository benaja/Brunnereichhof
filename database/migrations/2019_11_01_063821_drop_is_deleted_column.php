<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIsDeletedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
        Schema::table('project', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
        Schema::table('room', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
        Schema::table('inventar', function (Blueprint $table) {
            $table->dropColumn('isDeleted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
