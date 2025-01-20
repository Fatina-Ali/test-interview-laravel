<?php

namespace Database\Seeders;

use App\Models\SubCategoryModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubCategoryModel::firstOrCreate([
            'sub_category_name' => 'House Electric mentainance'
        ],[
            'sub_category_image' => 'user_default_image.png',
            'category_id'       => 2,
            'sub_category_slug' => 'House_Electric_mentainance'
        ]);

        SubCategoryModel::firstOrCreate([
            'sub_category_name' => 'Electric network'
        ],[
            'sub_category_image' => 'user_default_image.png',
            'category_id'       => 2,
            'sub_category_slug' => 'Electric_network'
        ]);
    }
}
