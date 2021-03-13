<?php

use App\Resource;
use App\ResourcePlannerDay;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateResourcePlannerDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        

        $dates = Resource::groupBy('date')
            ->get()
            ->map(fn ($resource) => [
                'date' => $resource->date,
                'completed' => true,
                'history_enabled' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ])->toArray();

        ResourcePlannerDay::insert($dates);

        DB::table('resources')
            ->join('resource_planner_days', 'resource_planner_days.date', '=', 'resources.date')
            ->update(['resource_planner_day_id' => DB::raw('resource_planner_days.id')]);
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
