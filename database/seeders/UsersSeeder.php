<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Обычный пользователь',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );
    }
}
