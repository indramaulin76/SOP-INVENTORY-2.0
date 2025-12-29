<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'pimpinan@saebakery.com',
                'role' => User::ROLE_PIMPINAN,
                'password' => Hash::make('password'),
                'active' => true,
            ],
            [
                'name' => 'Administrator',
                'email' => 'admin@saebakery.com',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('password'),
                'active' => true,
            ],
            [
                'name' => 'Staff Karyawan',
                'email' => 'karyawan@saebakery.com',
                'role' => User::ROLE_KARYAWAN,
                'password' => Hash::make('password'),
                'active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
