<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\UserType;
use App\Role;
use App\Inventar;
use App\Bed;
use App\Room;

class ExampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
            ],[
                'name' => 'rapport_write',
                'name_de' => 'Wochenrapport schreiben'
            ],[
                'name' => 'employee_preview_read',
                'name_de' => 'Mitarbeierverzeichnis einsehen (nur Vorschau)'
            ],[
                'name' => 'employee_read',
                'name_de' => 'Mitarbeiterverzeichnis einsehen mit Details'
            ],[
                'name' => 'employee_write',
                'name_de' => 'Mitarbeiterverzeuchnis schreiben'
            ],[
                'name' => 'customer_read',
                'name_de' => 'Kundenverzeichnis einsehen'
            ],[
                'name' => 'customer_write',
                'name_de' => 'Kundenverzeichnis schreiben'
            ],[
                'name' => 'roomdispositioner_read',
                'name_de' => 'Raumplaner einsehen'
            ],[
                'name' => 'roomdispositioner_write',
                'name_de' => 'Raumplaner schreiben'
            ],[
                'name' => 'hourrecord_read',
                'name_de' => 'Sundenangaben einsehen'
            ],[
                'name' => 'hourrecord_write',
                'name_de' => 'Sundenangaben schreiben'
            ], [
                'name' => 'worker_read',
                'name_de' => 'Hofmitarbeiter einsehen'
            ],[
                'name' => 'worker_write',
                'name_de' => 'Hofmitarbeiter schreiben'
            ],[
                'name' => 'settings_read',
                'name_de' => 'Einstellungen einsehen'
            ],[
                'name' => 'settings_write',
                'name_de' => 'Einstellungen schreiben'
            ],[
                'name' => 'timerecord_read_write',
                'name_de' => 'Zeiterfassung'
            ],[
                'name' => 'timereocrd_stats',
                'name_de' => 'Zeiterfassung Auswertung'
            ],]);

        $authorizationrules = DB::table('authorizationrule')->get();
        $adminRole = DB::table('role')->where('name', 'Admin')->first();

        foreach($authorizationrules as $authorizationrule) {
            DB::table('role_authorizationrule')->insert([
                'authorizationrule_id' => $authorizationrule->id,
                'role_id' => $adminRole->id
            ]);
        }

        $userTypeAdmin = DB::table('usertype')->where('name', 'superadmin')->first();
        $userTypeWorker = DB::table('usertype')->where('name', 'worker')->first();

        DB::table('user')->insert([
            [
                'email' => 'admin@outlook.com',
                'username' => 'admin',
                'firstname' => 'Admin',
                'lastname' => 'Muster',
                'isPasswordChanged' => true,
                'password' => Hash::make('123abc123'),
                'type_id' => $userTypeAdmin->id,
                'role_id' => null
            ],
            [
                'email' => 'benhu00@outlook.com',
                'username' => 'benaja.hunzinger',
                'firstname' => 'Benaja',
                'lastname' => 'Hunzinger',
                'isPasswordChanged' => true,
                'password' => Hash::make('123abc123'),
                'type_id' => $userTypeWorker->id,
                'role_id' => $adminRole->id
            ]
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
    }
}
