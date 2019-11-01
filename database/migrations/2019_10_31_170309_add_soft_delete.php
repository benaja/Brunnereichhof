<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('culture', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('customer', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('employee', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('hourrecords', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('hours', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('inventar', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('project', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('rapport', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('rapportdetail', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('reservation', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('room', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('timerecord', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('user', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('worktype', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('culture', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('hourrecords', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('hours', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('inventar', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('project', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('rapport', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('rapportdetail', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('reservation', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('room', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('timerecord', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('worktype', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
