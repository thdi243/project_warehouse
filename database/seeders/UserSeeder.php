<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'username' => 'operator',
                'email' => 'operator@gmail.com',
                'jabatan' => 'operator',
                'password' => bcrypt('12345678'),
                'departemen' => 'Warehouse',
                'bagian' => 'Warehouse Spareparts',
                'nik' => 1234567890,
            ],
            [
                'username' => 'foreman',
                'email' => 'foreman@gmail.com',
                'jabatan' => 'foreman',
                'password' => bcrypt('12345678'),
                'departemen' => 'Warehouse',
                'bagian' => 'Warehouse Spareparts',
                'nik' => 9876543210,
            ],
            [
                'username' => 'supervisor',
                'email' => 'supervisor@gmail.com',
                'jabatan' => 'supervisor',
                'password' => bcrypt('12345678'),
                'departemen' => 'Warehouse',
                'bagian' => 'Warehouse Spareparts',
                'nik' => 1122334455,
            ],
            [
                'username' => 'dept_head',
                'email' => 'dept_head',
                'jabatan' => 'dept_head',
                'password' => bcrypt('12345678'),
                'departemen' => 'Warehouse',
                'bagian' => 'Warehouse Spareparts',
                'nik' => 5566778899,
            ],
        ]);
    }
}
