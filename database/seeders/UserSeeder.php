<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@homie.com'],
            [
                'name' => 'Admin Homie Laundry',
                'email' => 'admin@homie.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create test user
        User::updateOrCreate(
            ['email' => 'test@homie.com'],
            [
                'name' => 'Test User',
                'email' => 'test@homie.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('Email: admin@homie.com');
        $this->command->info('Password: password');
    }
}
