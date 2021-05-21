<?php

use App\Employee;
use App\FamilyAllowance;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamilyAllowancesForExistingUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $employees = Employee::withTrashed()->get();

        foreach ($employees as $employee) {
            $familyAllowance = FamilyAllowance::create([]);
            $employee->familyAllowance()->save($familyAllowance);
        }

        $workers = User::workers()->withTrashed()->get();

        foreach ($workers as $worker) {
            $familyAllowance = FamilyAllowance::create([]);
            $worker->familyAllowance()->save($familyAllowance);
        }
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
