<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToRapportdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapportdetail', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->nullable()->after('contract_type')->constrained('customer');
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
            $table->dropColumn('customer_id');
        });
    }
}
