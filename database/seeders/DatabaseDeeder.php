<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseDeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ExampleDataSeeder::class);
    }
}
