<?php

use App\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeTransactionsMorphable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $query) {
            $query->renameColumn('employee_id', 'transactionable_id');

            $query->string('transactionable_type')->nullable()->after('employee_id');
        });

        DB::table('transactions')->update([
            'transactionable_type' => Employee::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $query) {
            $query->renameColumn('transactionable_id', 'employee_id');

            $query->dropColumn('transactionable_type');
        });
    }
}
