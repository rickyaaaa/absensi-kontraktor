<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User (Level 1)
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create sample Supervisor (Level 2)
        $supervisor = User::create([
            'name' => 'Budi Supervisor',
            'email' => 'supervisor@test.com',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
        ]);

        Employee::create([
            'user_id' => $supervisor->id,
            'role_level' => 2,
            'position' => 'Supervisor',
            'salary_type' => 'monthly',
            'base_salary' => 240000,
            'overtime_rate_per_minute' => 240000 / 480,
            'late_penalty_per_minute' => 240000 / 480,
        ]);

        // Create sample Worker (Level 3)
        $worker = User::create([
            'name' => 'Andi Tukang',
            'email' => 'worker@test.com',
            'password' => Hash::make('password'),
            'role' => 'worker',
        ]);

        Employee::create([
            'user_id' => $worker->id,
            'role_level' => 3,
            'position' => 'Worker',
            'salary_type' => 'weekly',
            'base_salary' => 200000,
            'overtime_rate_per_minute' => 200000 / 480,
            'late_penalty_per_minute' => 200000 / 480,
        ]);
    }
}
