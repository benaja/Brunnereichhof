<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResoucesPivotTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapportdetail', function (Blueprint $table) {
            $table->unsignedBigInteger('resource_id')->after('customer_id')->nullable()->constrained();
        });

        Schema::create('car_resource', function (Blueprint $table) {
            $table->unsignedBigInteger('car_id')->nullable()->constrained();
            $table->unsignedBigInteger('resource_id')->nullable()->constrained();

            $table->primary(['car_id', 'resource_id']);
        });

        Schema::create('resource_tool', function (Blueprint $table) {
            $table->unsignedBigInteger('resource_id')->nullable()->constrained();
            $table->unsignedBigInteger('tool_id')->nullable()->constrained();

            $table->primary(['resource_id', 'tool_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rapportdetail', function (Blueprint $table) {
            $table->dropColumn('resource_id');
        });

        Schema::dropIfExists('car_resource');

        Schema::dropIfExists('resource_tool');
    }
}
