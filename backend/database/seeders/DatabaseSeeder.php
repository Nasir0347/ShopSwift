<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        User::firstOrCreate([
            'email' => 'admin@shopswift.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        User::firstOrCreate([
            'email' => 'customer@shopswift.com',
        ], [
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // 2. Real Products (replaces old dummy data)
        $this->call(RealProductsSeeder::class);

        // 3. Orders (depends on users and products)
        $this->call(OrderSeeder::class);
    }
}
