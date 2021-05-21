<?php

use App\AuthorizationRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFamilyAllowancesRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        AuthorizationRule::create([
            'name' => 'family_allowance_read',
            'name_de' => 'Familienzulagen einsehen',
        ]);

        AuthorizationRule::create([
            'name' => 'family_allowance_write',
            'name_de' => 'Familienzulagen schreiben',
        ]);
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
