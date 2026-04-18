<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::updateOrCreate(
            ['code' => 'new'],
            ['name' => 'Новый']
        );

        Status::updateOrCreate(
            ['code' => 'in_progress'],
            ['name' => 'В работе']
        );

        Status::updateOrCreate(
            ['code' => 'delivering'],
            ['name' => 'Доставляется']
        );

        Status::updateOrCreate(
            ['code' => 'completed'],
            ['name' => 'Доставлено']
        );
    }
}
