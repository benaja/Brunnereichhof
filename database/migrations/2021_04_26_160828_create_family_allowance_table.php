<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamilyAllowanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('family_allowances', function (Blueprint $table) {
            $table->id();
            $table->string('civil_status')->nullable();
            $table->boolean('has_family_allowance')->default(false);
            $table->date('expiration_of_family_allowance')->nullable();
            $table->boolean('partner_employed')->nullable();
            $table->boolean('needs_e411_form')->nullable();
            $table->boolean('is_e411_handed_out')->nullable();
            $table->boolean('is_e411_submitted')->nullable();
            $table->boolean('has_marriage_document')->nullable();
            $table->boolean('has_divorce_document')->nullable();
            $table->boolean('it_registration_family_allowances_send')->nullable();
            $table->date('family_allowance_expiration_date')->nullable();
            $table->boolean('claim_id_received')->nullable();
            $table->date('claim_id_expiration_date')->nullable();
            $table->foreignId('family_allowanceable_id')->nullable();
            $table->string('family_allowanceable_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('family_allowances');
    }
}
