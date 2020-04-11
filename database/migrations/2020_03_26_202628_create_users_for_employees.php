<?php

use App\Employee;
use App\User;
use App\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersForEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('customer', function (Blueprint $table) {
        //     $table->dropForeign('customer_ibfk_1');
        // });

        // Schema::table('timerecord', function (Blueprint $table) {
        //     $table->dropForeign('timerecord_ibfk_1');
        // });

        // Schema::table('user', function (Blueprint $table) {
        //     $table->bigIncrements('id')->change();
        // });

        // Schema::table('employee', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->nullable();
        //     $table->foreign('user_id')->references('id')->on('user');
        // });

        // Schema::table('customer', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->change();
        //     $table->foreign('user_id')->references('id')->on('user');
        // });

        // Schema::table('timerecord', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->change();
        //     $table->foreign('user_id')->references('id')->on('user');
        // });

        UserType::firstOrCreate([
            'id' => 4,
            'name' => 'employee'
        ]);

        $employeeUserType = UserType::find(4);

        $employees = Employee::withTrashed()->get();
        foreach ($employees as $employee) {
            $password = str_random(8);
            $user = User::create([
                'firstname' => $employee->firstname,
                'lastname' => $employee->lastname,
                'username' => $employee->firstname . "." . $employee->lastname,
                'password' => Hash::make($password),
                'isPasswordChanged' => 0,
            ]);
            $user->employee()->save($employee);
            $employeeUserType->users()->save($user);
            if ($employee->deleted_at) {
                $user->delete();
            }
        }

        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('firstname');
            $table->dropColumn('lastname');
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
            $table->dropColumn('user_id');
        });
    }
}
