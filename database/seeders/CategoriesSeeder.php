<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::updateOrCreate(
            ['code' => 'pizza'],
            ['name' => 'Пиццы']
        );

        Category::updateOrCreate(
            ['code' => 'drink'],
            ['name' => 'Напитки']
        );
    }
}
