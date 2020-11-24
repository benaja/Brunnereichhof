<?php

use App\Hour;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CalculateDateAndDurationFromHours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Hour::join('timerecord', 'timerecord.id', '=', 'hours.timerecord_id')->update([
            'hours.date' => DB::raw('timerecord.date'),
            'duration' => DB::raw('TIME_TO_SEC(TIMEDIFF(hours.to, hours.from)) / 3600'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
