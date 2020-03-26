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
        Schema::table('employee', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('user');
        });

        $employeeUserType = UserType::crate([
            'name' => 'employee'
        ]);

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
            if ($employee->deleted_at) {
                $user->delete();
            }
            $employee->user()->save($user);
            $employee->$employeeUserType->users()->save($user);
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
