<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcePlannerDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_planner_days', function (Blueprint $table) {
            $table->id();
            $table->boolean('completed')->nullable()->default(false);
            $table->boolean('history_enabled')->nullable()->default(false);
            $table->date('date');
            $table->timestamps();
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->foreignId('resource_planner_day_id')
                ->nullable()
                ->after('rapport_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->dropColumn('completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['resource_planner_day_id']);
            $table->dropColumn('resource_planner_day_id');

            $table->boolean('completed')->nullable()->default(false)->after('comment');
        });

        Schema::dropIfExists('resource_planner_days');
    }
}
