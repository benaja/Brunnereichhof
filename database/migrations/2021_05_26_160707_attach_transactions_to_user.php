<?php

use App\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttachTransactionsToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $query) {
            $query->foreignId('employee_id')->nullable()->change();

            $query->renameColumn('employee_id', 'user_id');
        });

        DB::table('transactions')
            ->join('employee', 'employee.id', '=', 'transactions.user_id')
            ->update([
                'transactions.user_id' => DB::raw('employee.user_id'),
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
            $query->renameColumn('user_id', 'employee_id');
        });
    }
}
