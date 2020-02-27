<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDifferingBillingAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('street')->nullable();
            $table->string('place')->nullable();
            $table->string('plz')->nullable();
            $table->string('addition')->nullable();
            $table->timestamps();
        });

        Schema::table('customer', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->boolean('differingBillingAddress')->default(false);

            $table->foreign('address_id')->references('id')->on('address');
            $table->foreign('billing_address_id')->references('id')->on('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->dropForeign('customer_address_id_foreign');
            $table->dropForeign('customer_billing_address_id_foreign');
            $table->dropColumn('address_id');
            $table->dropColumn('billing_address_id');
            $table->dropColumn('differingBillingAddress');
        });

        Schema::dropIfExists('address');
    }
}
