<?php

use App\Employee;
use App\Enums\UserTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HandleEmployeeLoginActiveByActiveState extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->boolean('isLoginActive')->default(true)->nullable()->after('remember_token');
        });

        DB::table('employee')
            ->join('user', 'employee.user_id', '=', 'user.id')
            ->update([
                'user.deleted_at' => DB::raw('employee.deleted_at'),
                'user.isLoginActive' => DB::raw('employee.isLoginActive'),
            ]);

        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('isLoginActive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee', function (Blueprint $table) {
            $table->boolean('isLoginActive')->default(false)->nullable()->after('resource_planner_white_listed');
        });

        DB::table('employee')
            ->join('user', 'employee.user_id', '=', 'user.id')
            ->update([
                'employee.isLoginActive' => DB::raw('user.isLoginActive'),
            ]);

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('isLoginActive');
        });
    }
}
