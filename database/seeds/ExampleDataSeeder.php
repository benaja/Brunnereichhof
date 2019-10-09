<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\UserType;
use App\Role;
use App\Inventar;
use App\Bed;
use App\Room;
use App\Worktype;
use App\User;
use App\Customer;
use App\Project;

class ExampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reservation')->delete();
        DB::table('customer_project')->delete();
        DB::table('customer')->delete();
        DB::table('user')->delete();
        DB::table('role_authorizationrule')->delete();
        DB::table('authorizationrule')->delete();

        UserType::firstOrCreate(['name' => 'customer']);
        UserType::firstOrCreate(['name' => 'worker']);
        UserType::firstOrCreate(['name' => 'superadmin']);

        Role::firstOrCreate(['name' => 'Gruppenführer']);
        Role::firstOrCreate(['name' => 'Admin']);

        DB::table('authorizationrule')->insert([
            [
                'name' => 'rapport_read',
                'name_de' => 'Wochenrapport einsehen'
            ], [
                'name' => 'rapport_write',
                'name_de' => 'Wochenrapport schreiben'
            ], [
                'name' => 'employee_preview_read',
                'name_de' => 'Mitarbeierverzeichnis einsehen (nur Vorschau)'
            ], [
                'name' => 'employee_read',
                'name_de' => 'Mitarbeiterverzeichnis einsehen mit Details'
            ], [
                'name' => 'employee_write',
                'name_de' => 'Mitarbeiterverzeuchnis schreiben'
            ], [
                'name' => 'customer_read',
                'name_de' => 'Kundenverzeichnis einsehen'
            ], [
                'name' => 'customer_write',
                'name_de' => 'Kundenverzeichnis schreiben'
            ], [
                'name' => 'roomdispositioner_read',
                'name_de' => 'Raumplaner einsehen'
            ], [
                'name' => 'roomdispositioner_write',
                'name_de' => 'Raumplaner schreiben'
            ], [
                'name' => 'hourrecord_read',
                'name_de' => 'Sundenangaben einsehen'
            ], [
                'name' => 'hourrecord_write',
                'name_de' => 'Sundenangaben schreiben'
            ], [
                'name' => 'worker_read',
                'name_de' => 'Hofmitarbeiter einsehen'
            ], [
                'name' => 'worker_write',
                'name_de' => 'Hofmitarbeiter schreiben'
            ], [
                'name' => 'settings_read',
                'name_de' => 'Einstellungen einsehen'
            ], [
                'name' => 'settings_write',
                'name_de' => 'Einstellungen schreiben'
            ], [
                'name' => 'timerecord_read_write',
                'name_de' => 'Zeiterfassung'
            ], [
                'name' => 'timerecord_stats',
                'name_de' => 'Zeiterfassung Auswertung'
            ], [
                'name' => 'evaluation_customer',
                'name_de' => 'Auswertungen Kunden'
            ], [
                'name' => 'evaluation_employee',
                'name_de' => 'Mitarbeiter Auswertung'
            ],
        ]);

        $authorizationrules = DB::table('authorizationrule')->get();
        $adminRole = DB::table('role')->where('name', 'Admin')->first();

        foreach ($authorizationrules as $authorizationrule) {
            DB::table('role_authorizationrule')->insert([
                'authorizationrule_id' => $authorizationrule->id,
                'role_id' => $adminRole->id
            ]);
        }

        $userTypeAdmin = DB::table('usertype')->where('name', 'superadmin')->first();
        $userTypeWorker = DB::table('usertype')->where('name', 'worker')->first();
        $userTypeCustomer = DB::table('usertype')->where('name', 'customer')->first();


        User::firstOrCreate([
            'email' => 'admin@outlook.com',
            'username' => 'admin',
            'firstname' => 'Admin',
            'lastname' => 'Muster',
        ], [
            'isPasswordChanged' => true,
            'password' => Hash::make('123abc123'),
            'type_id' => $userTypeAdmin->id,
            'role_id' => null
        ]);
        User::firstOrCreate([
            'email' => 'benhu00@outlook.com',
            'username' => 'benaja.hunzinger',
            'firstname' => 'Benaja',
            'lastname' => 'Hunzinger'
        ], [
            'isPasswordChanged' => true,
            'password' => Hash::make('123abc123'),
            'type_id' => $userTypeWorker->id,
            'role_id' => $adminRole->id
        ]);

        $kissen = Inventar::firstOrCreate([
            'name' => 'Kissen',
            'price' => 20
        ]);
        $leintuch = Inventar::firstOrCreate([
            'name' => 'Leintuch',
            'price' => 8
        ]);

        $doubleBed = Bed::firstOrCreate([
            'name' => '2er Bett',
            'width' => '180cm',
            'places' => 2
        ]);
        $singleBed = Bed::firstOrCreate([
            'name' => '1er Bett',
            'width' => '80cm',
            'places' => 1
        ]);

        $doubleBed->inventars()->sync([
            $kissen->id => ['amount' => 2, 'amount_2' => 2],
            $leintuch->id => ['amount' => 1, 'amount_2' => 1]
        ]);
        $singleBed->inventars()->sync([
            $kissen->id => ['amount' => 2, 'amount_2' => 2],
            $leintuch->id => ['amount' => 2, 'amount_2' => 2]
        ]);

        $room1 = Room::firstOrCreate([
            'name' => '5er Zimmer',
            'number' => 3,
            'location' => 'Bern'
        ]);
        $room2 = Room::firstOrCreate([
            'name' => '2er Zimmer',
            'number' => 2,
            'location' => 'Zürich'
        ]);

        $room1->beds()->sync([$doubleBed->id, $doubleBed->id, $singleBed->id]);
        $room2->beds()->sync([$doubleBed->id]);

        DB::table('employee')->delete();
        DB::table('employee')->insert([[
            'firstname' => 'Man',
            'lastname' => 'Iron',
            'nationality' => 'CH',
            'isActive' => true,
            'isGuest' => false,
            'sex' => 'man   '
        ], [
            'firstname' => 'America',
            'lastname' => 'Captain',
            'nationality' => 'US',
            'isActive' => true,
            'isGuest' => false,
            'sex' => 'man'
        ]]);

        DB::table('settings')->delete();
        DB::table('settings')->insert([[
            'key' => 'fullDayShortStart',
            'value' => '08:00',
            'type' => 'string'
        ], [
            'key' => 'fullDayShortEnd',
            'value' => '16:00',
            'type' => 'string'
        ], [
            'key' => 'fullDayLongStart',
            'value' => '07:00',
            'type' => 'string'
        ], [
            'key' => 'fullDayLongEnd',
            'value' => '16:00',
            'type' => 'string'
        ]]);

        Worktype::firstOrCreate([
            'name' => 'productiveHours',
            'name_de' => 'Produktivstunden',
            'short_name' => '',
            'color' => 'primary'
        ]);
        Worktype::firstOrCreate([
            'name' => 'holidays',
            'name_de' => 'Ferien',
            'short_name' => 'F',
            'color' => 'yellow darken-3'
        ]);
        Worktype::firstOrCreate([
            'name' => 'sick',
            'name_de' => 'Krank',
            'short_name' => 'K',
            'color' => 'red'
        ]);
        Worktype::firstOrCreate([
            'name' => 'accident',
            'name_de' => 'Unfall',
            'short_name' => 'U',
            'color' => 'red'
        ]);
        Worktype::firstOrCreate([
            'name' => 'school',
            'name_de' => 'Schule',
            'short_name' => 'S',
            'color' => 'blue'
        ]);

        DB::table('project')->delete();
        DB::table('project')->insert([[
            'name' => 'Allgemein',
            'description' => 'Standart Projekt'
        ], [
            'name' => 'Jätten',
            'description' => 'Jätten auf den Feldern'
        ]]);

        $customerUser = User::firstOrCreate([
            'email' => 'max.muster@test.com',
            'username' => 'max.muster',
            'firstname' => 'Max',
            'lastname' => 'Muster'
        ], [
            'isPasswordChanged' => true,
            'password' => Hash::make('123abc123'),
            'type_id' => $userTypeCustomer->id,
        ]);
        $customer = Customer::firstOrCreate([
            'firstname' => 'Max',
            'lastname' => 'Muster',
            'addition' => '',
            'street' => 'Hauptstrasse 10',
            'place' => 'Bern',
            'plz' => 3000,
            'secret' => encrypt('123abc123'),
            'user_id' => $customerUser->id,
        ]);
        $defaultProject = Project::where('name', 'Allgemein')->first();
        $customer->projects()->save($defaultProject);
        $customer->save();
    }
}
