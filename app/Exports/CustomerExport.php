<?php

namespace App\Exports;

use App\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromCollection, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Customer::all();
    }

    public function map($customer): array
    {
        return [
            $customer->customer_number,
            $customer->lastname,
            $customer->firstname,
            $customer->street,
            $customer->plz,
            $customer->place,
            $customer->user->email,
            $customer->secret ? decrypt($customer->secret) : ""
        ];
    }
}
