<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResourcePlannerWhitelistToEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee', function (Blueprint $table) {
            $table->string('function')->nullable()->after('allergy');
            $table->boolean('resource_planner_white_listed')->nullable()->default(false)->after('function');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('function');
            $table->dropColumn('resource_planner_white_listed');
        });
    }
}
