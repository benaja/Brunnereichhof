<?php

use App\Address;
use App\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $customers = Customer::withTrashed()->get();
        foreach ($customers as $customer) {
            $address = Address::create([
                'place' => $customer->place,
                'plz' => $customer->plz,
                'street' => $customer->street,
                'addition' => $customer->addition,
            ]);
            $customer->address()->associate($address);
            $customer->save();
        }

        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn('place');
            $table->dropColumn('plz');
            $table->dropColumn('street');
            $table->dropColumn('addition');
        });
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
