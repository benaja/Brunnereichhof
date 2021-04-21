<?php

use App\AuthorizationRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRulesForResourcePlanner extends Migration
{
    private $rules = [
        'resource_planner_read' => 'Einsatzplaner Planung einsehen',
        'resource_planner_write' => 'Einsatzplaner Planung erstellen und bearbeiten',
        'tools_read' => 'Einsatzplaner Werkzeuge einsehen',
        'tools_write' => 'Einsatzplaner Werkzeuge schreiben',
        'cars_read' => 'Einsatzplaner Autos einsehen',
        'cars_write' => 'Einsatzplaner Autos schreiben',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (array_keys($this->rules) as $key) {
            AuthorizationRule::create([
                'name' => $key,
                'name_de' => $this->rules[$key],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AuthorizationRule::whereIn('name', array_keys($this->rules))
            ->delete();
    }
}
