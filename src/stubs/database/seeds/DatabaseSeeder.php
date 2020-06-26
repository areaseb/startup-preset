<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StartSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(CitiesSeeder::class);

    }
}
