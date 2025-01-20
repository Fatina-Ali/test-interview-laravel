<?php

namespace Database\Seeders;

use App\Models\Restriction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestrictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restriction1 = Restriction::firstOrCreate([
            'name' => 'Aerosols',
        ],[]);

        $restriction2 = Restriction::firstOrCreate([
            'name' => 'Air bags',
        ],[]);

        $restriction3 = Restriction::firstOrCreate([
            'name' => 'Ammunition',
        ],[]);

        $restriction4 = Restriction::firstOrCreate([
            'name' => 'Dry Ice',
        ],[]);

        //link to countries
        $restriction1->countries()->attach([1]);
        $restriction1->countries()->attach([2]);
        $restriction2->countries()->attach([1]);
        $restriction2->countries()->attach([2]);
        $restriction3->countries()->attach([3]);
        $restriction3->countries()->attach([4]);
        $restriction4->countries()->attach([3]);
        $restriction4->countries()->attach([4]);
    }
}
