<?php

namespace Database\Seeders;

use App\Models\CategoryModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryModel::firstOrCreate([
            'category_name' => 'Electric mentainance'
        ],[
            'category_image' => 'user_default_image.png',
            'category_slug' => 'Electric_mentainance',
        ]);
        CategoryModel::firstOrCreate([
            'category_name' => 'Car Electric mentainance'
        ],[
            'category_image' => 'user_default_image.png',
            'category_slug' => 'car_lectric_mentainance',
        ]);
    }
}
