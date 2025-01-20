<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Prohibition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProhibitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prohibition1 = Prohibition::firstOrCreate([
            'name' => 'Aerosols',
        ],[]);

        $prohibition2 = Prohibition::firstOrCreate([
            'name' => 'Air bags',
        ],[]);

        $prohibition3 = Prohibition::firstOrCreate([
            'name' => 'Ammunition',
        ],[]);

        $prohibition4 = Prohibition::firstOrCreate([
            'name' => 'Dry Ice',
        ],[]);

        //link to countries
        $prohibition1->countries()->attach([1]);
        $prohibition1->countries()->attach([2]);
        $prohibition2->countries()->attach([1]);
        $prohibition2->countries()->attach([2]);
        $prohibition3->countries()->attach([3]);
        $prohibition3->countries()->attach([4]);
        $prohibition4->countries()->attach([3]);
        $prohibition4->countries()->attach([4]);



    }
}
