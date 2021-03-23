<?php

use App\Rapportdetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplyCustomerIdToAllRapportdetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Rapportdetail::join('rapport', 'rapport.id', '=', 'rapportdetail.rapport_id')
            ->update([
                'rapportdetail.customer_id' => DB::raw('rapport.customer_id'),
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
