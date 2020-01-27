<?php

namespace App\Console\Commands;

use App\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:fixpassword';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cusotmers = Customer::whereNotNull('secret')->get();
        foreach ($cusotmers as $customer) {
            $secret = decrypt($customer->secret);
            $this->info($secret);
            $customer->user->password = Hash::make($secret);
            $customer->user->save();
        }
    }
}
