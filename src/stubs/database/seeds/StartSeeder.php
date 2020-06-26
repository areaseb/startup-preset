<?php

use Illuminate\Database\Seeder;
use App\Classes\Contacts\{Contact, Company};
use App\Classes\{Role, User};


class StartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin = Role::create([
            'name' => 'Admin',
            'slug' => 'admin'
        ]);


        $user = User::create([
            'email' => 'giacomo.gasperini@gmail.com',
            'password' => bcrypt('83674trf%*9op[]')
        ]);

        $user->roles()->attach($admin);

        Company::create([
            'rag_soc' => '2gWebDeveloper',
            'indirizzo' => 'Via Broli 7',
            'cap' => '36020',
            'citta' => 'Valbrenta',
            'provincia' => 'Vicenza',
            'piva' => '03882900248',
            'partner' => 1,
            'telefono' => '+393421967852',
            'email' => 'giacomo.gasperini@gmail.com',
        ]);

        Contact::create([
            'nome' => 'Giacomo',
            'cognome' => 'Gasperini',
            'cellulare' => '+393421967852',
            'email' => 'giacomo.gasperini@gmail.com',
            'indirizzo' => 'Via Broli 7',
            'cap' => '36020',
            'citta' => 'Valbrenta',
            'provincia' => 'Vicenza',
            'user_id' => 1,
            'company_id' => 1,
            'city_id' => 3180
        ]);


    }
}
