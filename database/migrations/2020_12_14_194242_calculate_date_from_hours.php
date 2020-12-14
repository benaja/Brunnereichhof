<?php

use App\Hour;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CalculateDateFromHours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Hour::whereNull('hours.date')->join('timerecord', 'timerecord.id', '=', 'hours.timerecord_id')->update([
            'hours.date' => DB::raw('timerecord.date'),
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
