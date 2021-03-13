<?php

use App\Employee;
use App\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateLanguages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $german = Language::firstOrCreate([ 'name' => 'Deutsch']);
        $english = Language::firstOrCreate([ 'name' => 'Englisch']);
        Language::firstOrCreate([ 'name' => 'Französisch']);

        $employees = Employee::all();
        foreach($employees as $employee) {
            if ($employee->german_knowledge) {
                $employee->languages()->attach($german);
            }
            if ($employee->english_knowledge) {
                $employee->languages()->attach($english);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
