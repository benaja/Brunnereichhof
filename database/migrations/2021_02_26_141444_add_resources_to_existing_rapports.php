<?php

use App\Car;
use App\Rapport;
use App\Rapportdetail;
use App\Resource;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddResourcesToExistingRapports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $resouces = Rapportdetail::groupBy('date', 'rapport_id')
            ->get()
            ->map(function ($rapportdetail) {
                return [
                    'date' => $rapportdetail->date,
                    'customer_id' => $rapportdetail->customer_id,
                    'rapport_id' => $rapportdetail->rapport_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->values()->toArray();

        Resource::insert($resouces);

        Rapportdetail::join('rapport', 'rapport.id', '=', 'rapportdetail.rapport_id')
            ->join('resources', 'resources.rapport_id', '=', 'rapport.id')
            ->update([
                'rapportdetail.resource_id' => DB::raw('resources.id'),
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
